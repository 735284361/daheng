<?php

namespace App\Services;

use App\Http\Requests\OrderRequest;
use App\Jobs\CloseOrder;
use App\Jobs\CompleteOrder;
use App\Models\Goods;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderGoods;
use App\Models\UserBill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderService
{

    protected $payService;
    protected $goodsService;
    protected $order;


    public function __construct()
    {
        $this->payService = new PayService();
        $this->goodsService = new GoodsService();
    }

    /**
     * 生成订单
     * @param OrderRequest $request
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(OrderRequest $request)
    {
        $goods = json_decode($request->goodsJsonStr,true);

        if (empty($goods)) {
            return ['code' => 1, 'msg' => '商品不能为空'];
        }
        $orderNo = Order::getOrderNo(Order::PRE_BUY);
        // 商品数量
        $goodsCollect = collect($goods);
        $productCount = $goodsCollect->sum('number');

        // 生成订单商品
        $amountTotal = 0;
        $goodsList = [];
//        $body = Goods::find($goods[0]['goodsId'])->value('name');
        for ($i = 0; $i < count($goods); $i++) {
            // 获取商品属性
            $sku = $this->goodsService->getSku($goods[$i]['goodsId'],$goods[$i]['propertyChildIds']);
            // 判断商品库存
            if ($goods[$i]['number'] > $sku->stock) {
                return ['code' => 1, 'msg' => '商品库存不足'];
            }
            // 计算订单总价
            $amountTotal += $goods[$i]['number'] * $sku->price;
            // 订单包含的商品
            $good['order_no'] = $orderNo;
            $good['user_id'] = auth('api')->id();
            $good['goods_id'] = $goods[$i]['goodsId'];
            $good['sku'] = $goods[$i]['propertyChildName'];
            $good['property_id'] = $goods[$i]['propertyChildIds'];
            $good['product_count'] = $goods[$i]['number'];
            $good['product_price'] = $sku->price;
            $good['dist_price'] = $sku->dist_price;
            $good['created_at'] = Carbon::now();
            $good['updated_at'] = Carbon::now();
            $goodsList[] = $good;
        }
        // 计算运费
        $shippingFeeService = new ShippingFeeService();
        $logisticsFee = $shippingFeeService->getShippingFee($request->province, $amountTotal);
        // 订单总金额
        $totalFee = $amountTotal + $logisticsFee;

        DB::beginTransaction();
        $order = new Order();
        // 添加订单记录
        $order->order_no = $orderNo;
        $order->user_id = auth('api')->id();
        $order->product_count = $productCount;
        $order->product_amount_total = $amountTotal;
        $order->logistics_fee = $logisticsFee;
        $order->order_amount_total = $totalFee;
        $order->remark = $request->remark;
        $orderRes = $order->save();
        $this->order = $order;
        // 添加订单物品
        $orderGoodsRes = OrderGoods::insert($goodsList);
        // 添加订单日志
        $orderEventRes = $this->saveEventLog(Order::STATUS_UNPAID);
        // 添加订单地址
        $orderAddressRes = OrderAddress::create([
            'order_no' => $orderNo,
            'name' => $request->name,
            'phone' => $request->phone,
            'province' => $request->province,
            'city' => $request->city,
            'county' => $request->county,
            'detail_info' => $request->detail_info,
            'postal_code' => $request->postal_code
        ]);
        // 发起支付
//        $totalFee = 0.01;
        $payParams = $this->payService->getPayParams($orderNo, $totalFee);

        if ($orderRes && $orderGoodsRes && $orderEventRes &&
            $orderAddressRes && $payParams['code'] == 0) {

            // 定时关闭订单
            CloseOrder::dispatch($order);
            DB::commit();
            return ['code' => 0, 'msg' => 'Success', 'data' => $payParams['result']];
        } else {
            DB::rollBack();
            return ['code' => 1, 'msg' => 'Fail'];
        }
    }

    /**
     * 支付成功
     * @param Order $order
     * @param null $remark
     * @return bool
     * @throws \Throwable
     */
    public function paySuccess(Order $order, $remark = null)
    {
        $this->order = $order;
        // 只有订单状态为待支付 才进行订单关闭的操作
        if ($this->order->status != Order::STATUS_UNPAID) {
            return;
        }
        $exception = DB::transaction(function() use($remark) {
            $status = Order::STATUS_PAID;
            // 保存订单
            $this->updateOrderStatus($status, $remark);
            // 订单对应的商品
            $goodsList = OrderGoods::with('goods')->where('order_no',$this->order->order_no)->get();
            // 处理商品的数据统计
            $billName = '';
            foreach ($goodsList as $goods) {
                $this->goodsService->dealGoodsCount($goods->goods_id, $goods->product_count, $goods->property_id);
                $billName .= $goods->goods->name;
            }
            // 更新资金流水记录表
            $this->saveBillInfo($this->order->user_id, $billName, $this->order->order_amount_total, UserBill::AMOUNT_TYPE_EXPEND,
                UserBill::BILL_STATUS_NORMAL, UserBill::BILL_TYPE_BUY);
            // 保存订单和代理的关系
            AgentService::saveAgentOrderMap($this->order);
            // 支付成功 进入消息发送系统
            $this->sendMsg($status);
            // 定时结束订单任务
            CompleteOrder::dispatch($this->order);
        });
        return $exception ? false : true;
    }

    /**
     * 重新支付订单
     * @param $orderId
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function repay($orderId)
    {
        $order = Order::find($orderId);
        if ($order) {
            if ($order->status != Order::STATUS_UNPAID) {
                return ['code' => 1,'msg' => '订单失效'];
            }
            // 获取支付信息
            $payInfo = $this->payService->getPayParams($order->order_no, $order->order_amount_total);
            if ($payInfo['code'] == 0) {
                return ['code' => 0,'msg' => 'Success','data' => $payInfo['result']];
            }
        }
        return ['code' => 1,'msg' => '发起支付失败'];
    }

    /**
     * 关闭订单
     * @param Order $order
     * @param null $remark
     * @return bool
     * @throws \Throwable
     */
    public function closeOrder(Order $order, $remark = null)
    {
        $this->order = $order;
        $exception = DB::transaction(function() use($remark) {
            $status = Order::STATUS_ORDER_CLOSE;
            // 修改订单状态
            $this->updateOrderStatus($status, $remark);
            // 支付成功 进入消息发送系统
            $this->sendMsg($status);
        });
        return $exception ? false : true;
    }

    /**
     * 发货
     * @param Order $order
     * @param $deliveryCompany
     * @param $deliveryNumber
     * @return bool
     * @throws \Throwable
     */
    public function deliverGoods(Order $order,$deliveryCompany, $deliveryNumber)
    {
        $this->order = $order;
        $exception = DB::transaction(function() use ($deliveryCompany, $deliveryNumber) {
            $status = Order::STATUS_SHIPPED;
            // 保存快递信息
            $this->updateOrderStatus($status);
            // 保存快递信息
            $this->saveDeliveryInfo($deliveryCompany, $deliveryNumber);
            // 支付成功 进入消息发送系统
            $this->sendMsg($status);
        });
        return $exception ? false : true;
    }

    /**
     * 支付失败
     * @param Order $order
     * @return bool
     * @throws \Throwable
     */
    public function payFailed(Order $order)
    {
        $this->order = $order;

        // 只有订单状态为待支付 才进行订单关闭的操作
        if ($this->order->status != Order::STATUS_UNPAID) {
            return;
        }
        $exception = DB::transaction(function() {
            $status = Order::STATUS_PAY_FAILED;
            // 保存订单
            $this->updateOrderStatus($status);
            // 支付成功 进入消息发送系统
            $this->sendMsg($status);
        });
        return $exception ? false : true;
    }

    /**
     * 确认收货
     * @param Order $order
     * @return bool
     * @throws \Throwable
     */
    public function confirmOrder(Order $order)
    {
        $this->order = $order;

        $exception = DB::transaction(function() {
            $status = Order::STATUS_RECEIVED;
            // 保存订单
            $this->updateOrderStatus($status);
            // 支付成功 进入消息发送系统
            $this->sendMsg($status);
        });
        return $exception ? false : true;
    }

    /**
     * 结束订单
     * @param Order $order
     * @param null $remark
     * @return bool
     * @throws \Throwable
     */
    public function completeOrder(Order $order, $remark = null)
    {
        $this->order = $order;
        // 订单结束
        // 只有订单状态为已支付 才进行订单关闭的操作
        $status = [
            Order::STATUS_PAID,
            Order::STATUS_SHIPPED,
            Order::STATUS_RECEIVED
        ];
        if (!in_array($this->order->status, $status)){
            return;
        }
        $exception = DB::transaction(function() use ($remark) {
            $status = Order::STATUS_COMPLETED;
            // 更新订单状态
            $this->updateOrderStatus($status, $remark);
            // 订单分成流程
            $agentService = new AgentService();
            $agentService->orderCommission($this->order->order_no);
            // 通知服务
            $this->sendMsg($status);
            return;
        });
        return $exception ? false : true;
    }

    /**
     * 更新订单状态
     * @param $status
     * @param $remark null
     * @return mixed
     */
    private function updateOrderStatus($status, $remark = null)
    {
        // 更新订单状态
        $this->order->status = $status;
        $this->order->save();
        // 更新订单日志
        $this->saveEventLog($status, $remark);
        return;
    }

    /**
     * 保存订单日志
     * @param $event
     * @param null $remark
     * @return mixed
     */
    private function saveEventLog($event, $remark = null)
    {
        return $this->order->eventLogs()->create([
            'order_no' => $this->order->order_no,
            'event' => $event,
            'remark' => $remark
        ]);
    }

    /**
     * 存储资金流水
     * @param $userId
     * @param $billName
     * @param $amount
     * @param $amountType
     * @param $status
     * @param $billType
     * @return mixed
     */
    public function saveBillInfo($userId, $billName, $amount, $amountType, $status, $billType)
    {
        return $this->order->bill()->create([
            'user_id' => $userId,
            'bill_name' => $billName,
            'amount' => $amount,
            'amount_type' => $amountType,
            'status' => $status,
            'bill_type' => $billType
        ]);
    }

    /**
     * 消息发送
     * @param $status
     */
    public function sendMsg($status)
    {
        MessageService::orderMsg($this->order,$status);
    }

    /**
     * 更新快递信息
     * @param $company
     * @param $number
     * @return mixed
     */
    private function saveDeliveryInfo($company, $number)
    {
        $this->order->address->delivery_company = $company;
        $this->order->address->delivery_number = $number;
        return $this->order->address->save();
    }

    /**
     * 订单状态分类统计
     * @return mixed
     */
    public function statistics()
    {
        return Order::where('user_id',auth('api')->id())->selectRaw('status, count(*) count')->groupBy('status')->get();
    }

    /**
     * 获取订单列表
     * @return Order[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function orderList()
    {
        $status = [
            Order::STATUS_UNPAID,
            Order::STATUS_PAID,
            Order::STATUS_SHIPPED,
            Order::STATUS_RECEIVED,
            Order::STATUS_COMPLETED
        ];
        $list = Order::with('goods')
            ->where('user_id',auth('api')->id())
            ->whereIn('status',$status)
            ->orderBy('id','desc')
            ->get();
        return $list;
    }

    /**
     * 订单详情
     * @param $id
     * @return Order|Order[]
     */
    public function orderDetail($id)
    {
        return Order::with('goods')->with('eventLogs')->with('address')->find($id);
    }

    /**
     * 订单评论
     * @param Request $request
     * @return bool
     * @throws \Throwable
     */
    public function reputation(Request $request)
    {
        $data = $request->postJsonString;
        if ($data && !empty($data)) {
            $data = json_decode($data,true);
            $exception = DB::transaction(function () use ($data) {
                $orderId = $data['orderId'];
                // 修改订单评论状态
                $order = Order::find($orderId);
                $this->completeOrder($order,'用户评论完成');
                // 更新订单评论状态
                $order->comment_status = Order::COMMENTED;
                $order->save();
                // 更新订单对应商品的评论
                $reputations = $data['reputations'];
                foreach ($reputations as $reputation) {
                    $this->updateGoodsReputation($reputation['id'],$reputation['reputation'],$reputation['remark']);
                }
            });
            return $exception ? false : true;
        }
        return false;
    }

    /**
     * 更新商品评论和商品的评分
     * @param $id
     * @param $score
     * @param $comment
     */
    public function updateGoodsReputation($id, $score, $comment)
    {
        // 更新商品评论信息
        $orderGoods = OrderGoods::find($id);
        $orderGoods->score = $score;
        $orderGoods->comment = $comment;
        $orderGoods->save();
        // 更新商品总评分
        $goods = Goods::find($orderGoods->goods_id);
        $goods->number_reputation = $goods->number_reputation + 1;
        $goods->number_score = $goods->number_score + $score;
        $goods->save();
        return;
    }

}
