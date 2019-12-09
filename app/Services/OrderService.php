<?php

namespace App\Services;

use App\Http\Requests\OrderRequest;
use App\Jobs\CompleteOrder;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderGoods;
use App\Models\UserBill;
use Carbon\Carbon;
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
        $order = new Order();
        $orderNo = $order->getOrderNo(Order::PRE_BUY);
        // 商品数量
        $goodsCollect = collect($goods);
        $productCount = $goodsCollect->sum('number');

        // 生成订单商品
        $amountTotal = 0;
        $goodsList = [];
        for ($i = 0; $i < count($goods); $i++) {
            // 获取商品属性
            $sku = $this->goodsService->getSku($goods[$i]['goodsId'],$goods[$i]['propertyChildIds']);
            // 判断商品库存
            if ($goods[$i]['number'] > $sku['stock']) {
                return ['code' => 1, 'msg' => '商品库存不足'];
            }
            // 计算订单总价
            $amountTotal += $goods[$i]['number'] * $sku['price'];
            // 订单包含的商品
            $good['order_no'] = $orderNo;
            $good['goods_id'] = $goods[$i]['goodsId'];
            $good['sku'] = $goods[$i]['propertyChildName'];
            $good['property_id'] = $goods[$i]['propertyChildIds'];
            $good['product_count'] = $goods[$i]['number'];
            $good['product_price'] = $sku['price'];
            $good['dist_price'] = $sku['dist_price'];
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
        $payParams = $this->payService->getPayParams($orderNo,$totalFee);

        if ($orderRes && $orderGoodsRes && $orderEventRes &&
            $orderAddressRes && $payParams['code'] == 0) {
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
            $this->updateOrderStatus($status);
            // 更新资金流水记录表
            $this->saveBillInfo($this->order->user_id, $this->order->order_amount_total, UserBill::AMOUNT_TYPE_EXPEND,
                UserBill::BILL_STATUS_NORMAL, UserBill::BILL_TYPE_BUY);
            // 更新订单日志
            $this->saveEventLog($status, $remark);
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
     * 关闭订单
     * @param Order $order
     * @param $remark
     * @return bool
     * @throws \Throwable
     */
    public function closeOrder(Order $order, $remark)
    {
        $this->order = $order;
        $exception = DB::transaction(function() use($remark) {
            $status = Order::STATUS_ORDER_CLOSE;
            // 修改订单状态
            $this->updateOrderStatus($status);
            // 更新订单日志
            $this->saveEventLog($status, $remark);
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
            // 更新订单日志
            $this->saveEventLog($status);
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
            // 更新订单日志
            $this->saveEventLog($status);
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
            $this->updateOrderStatus($status);
            // 更新订单日志
            $this->saveEventLog($status, $remark);
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
     * @return mixed
     */
    private function updateOrderStatus($status)
    {
        $this->order->status = $status;
        return $this->order->save();
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
     * @param $amount
     * @param $amountType
     * @param $status
     * @param $billType
     * @return mixed
     */
    public function saveBillInfo($userId, $amount, $amountType, $status, $billType)
    {
        return $this->order->bill()->create([
            'user_id' => $userId,
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

    public function statistics()
    {
        return Order::where('user_id',auth('api')->id())->selectRaw('status, count(*) count')->groupBy('status')->get();
    }

}
