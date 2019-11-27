<?php

namespace App\Services;

use App\Http\Requests\OrderRequest;
use App\Models\GoodsSku;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderEventLog;
use App\Models\OrderGoods;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderService
{

    protected $payService;
    protected $goodsService;


    public function __construct()
    {
        $this->payService = new PayService();
        $this->goodsService = new GoodsService();
    }

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

        // TODO 添加用户账单 支付成功 发送
        // TODO 添加分销信息 支付成功处理
        // TODO 消息分发 支付成功处理

    }
}
