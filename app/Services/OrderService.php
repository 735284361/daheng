<?php

namespace App\Services;

use App\Http\Requests\OrderRequest;
use App\Models\AgentMember;
use App\Models\AgentOrderMaps;
use App\Models\GoodsSku;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderEventLog;
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
        // 添加订单物品
        $orderGoodsRes = OrderGoods::insert($goodsList);
        // 添加订单日志
        $orderEventRes = OrderEventLog::create([
            'order_no' => $orderNo,
            'event' => OrderEventLog::ORDER_CREATED
        ]);
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

    public function orderPaySuccess()
    {

    }

    /**
     * 结束订单
     * @param Order $order
     * @throws \Throwable
     */
    public function completeOrder(Order $order)
    {
        $this->order = $order;
        // 订单结束
        // 只有订单状态为已支付 才进行订单关闭的操作
        if ($this->order->status != Order::STATUS_PAID) {
            return;
        }

        DB::transaction(function() {
            // 更新订单状态
            $this->order->status = Order::STATUS_COMPLETED;
            $this->order->save();
            // 更新订单日志
            $this->order->eventLogs()->create([
                'order_no' => $this->order->order_no,
                'event' => OrderEventLog::ORDER_COMPLETED
            ]);

            // 订单分成流程
            $agentInfo = AgentOrderMaps::where('order_no',$this->order->order_no)->first();
            if ($agentInfo) {
                // 更新代理商资金流水表
                $this->order->bill()->create([
                    'user_id' => $agentInfo->agent_id,
                    'amount' => $agentInfo->commission,
                    'amount_type' => UserBill::AMOUNT_TYPE_INCOME,
                    'status' => UserBill::BILL_STATUS_NORMAL,
                    'bill_type' => UserBill::BILL_TYPE_COMMISSION
                ]);
                // 增加用户代理的消费金额
                AgentMember::where('user_id',$agentInfo['user_id'])->increment('order_number');
                AgentMember::where('user_id',$agentInfo['user_id'])->increment('amount',$agentInfo->commission);
            }
            // 通知服务
            MessageService::orderCompleteMsg($this->order);

            return;
        });
    }


}
