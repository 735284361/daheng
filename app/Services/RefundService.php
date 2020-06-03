<?php

namespace App\Services;

use App\Models\AgentOrderMaps;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\RefundBill;
use App\Models\UserBill;
use Illuminate\Support\Facades\DB;

class RefundService
{

    public function refundFee($orderNo, $refundType, $refundDesc, $goodsArr = null)
    {
        // 订单信息
        $order = Order::where(['order_no'=>$orderNo])->first();

        if (!$order) {
            return ['code' => 1,'msg' => '该订单不存在'];
        }

        // 判断订单状态 已确认收货 无法退款
        $statusArr = [
            Order::STATUS_RECEIVED,
            Order::STATUS_COMPLETED
        ];
        if (in_array($order->status,$statusArr)) {
            return ['code' => 1,'msg' => '该订单已完成分成，无法进行退款'];
        }

        if ($refundType == RefundBill::REFUND_TYPE_GOODS_FEE) {
            if (empty($goodsArr)) return ['code' => 1,'msg' => '请选择需要退款的商品'];
        }

        // 全部退款
        $refundTotalFee = 0;
        $refundCommissionFee = 0;
        if ($refundType == RefundBill::REFUND_TYPE_ALL) {
            // 只要有退款金额 则不满足全部退款条件
            if ($order->refund_mark == 1) {
                return ['code' => 1,'msg' => '已存在退款，无法进行全部退款'];
            }
            $refundTotalFee = $order->order_amount_total;
            $goodsArr = $this->getOrderGoods($orderNo);
            $refundFeeInfo = $this->getOrderGoodsFeeInfo($goodsArr, $orderNo);
            if ($refundFeeInfo['code'] == 0) {
                $refundCommissionFee = $refundFeeInfo['data']['refund_commission_total'];
            } else {
                return $refundFeeInfo;
            }
        } else if ($refundType == RefundBill::REFUND_TYPE_LOGISTICS_FEE ) {
            // 只要有运费退款金额 则不满足退款条件
            if ($order->refund_logistics_fee > 0) {
                return ['code' => 1,'msg' => '运费已退，无法进行该退款'];
            }
            if ($order->logistics_fee == 0) {
                return ['code' => 1,'msg' => '包邮订单，无法退运费'];
            }
            $refundTotalFee = $order->logistics_fee;
        } else {
            // 商品退款
            $refundFeeInfo = $this->getOrderGoodsFeeInfo($goodsArr, $orderNo);
            if ($refundFeeInfo['code'] == 0) {
                $refundTotalFee = $refundFeeInfo['data']['refund_total'];
                $refundCommissionFee = $refundFeeInfo['data']['refund_commission_total'];
            } else {
                return $refundFeeInfo;
            }
        }

        // 退款单号
        $refundNo = $this->getRefundNo();
        // 退款
        $refundRes = $this->refund($orderNo, $refundNo, $order->order_amount_total, $refundTotalFee, $refundDesc);
        // 检查退款是否成功
        if ($refundRes['return_code'] == 'SUCCESS' && $refundRes['result_code'] == 'SUCCESS') {
//        if (true) {
            // 判断订单退款是否已完
            $exception = DB::transaction(function () use (
                $orderNo,
                $refundNo,
                $order,
                $refundTotalFee,
                $refundCommissionFee,
                $refundType,
                $refundDesc,
                $goodsArr
            ) {
                // 更新订单商品表
                if ($refundType != RefundBill::REFUND_TYPE_LOGISTICS_FEE) {
                    $this->updateOrderGoods($goodsArr);
                }
                // 更新订单记录表
                $this->updateOrderRefundInfo($order, $refundTotalFee, $refundType, $refundCommissionFee);
                // 更新退款记录表
                $refundBill = $this->saveRefundBill($orderNo, $refundNo, $refundType, $refundTotalFee, $refundDesc);
                // 更新代理分销信息
                if ($refundType != RefundBill::REFUND_TYPE_LOGISTICS_FEE) {
                    $this->updateAgentOrder($orderNo, $refundCommissionFee);
                }
                // 用户退款账单
                $billName = $order->order_name.'-'.RefundBill::getRefundType($refundType);
                // 保存资金流水
                UserBillService::saveBillInfo(
                    $refundBill,
                    $order->user_id,
                    $billName,
                    $refundTotalFee,
                    UserBill::AMOUNT_TYPE_INCOME,
                    UserBill::BILL_STATUS_NORMAL,
                    UserBill::BILL_TYPE_REFUND
                );
            });
            if (!is_null($exception)) {
                return ['code' => 1,'msg' => '退款失败'];
            }
            return ['code' => 0, 'msg' => '退款成功'];
        } else {
            return ['code' => 1,'msg' => $refundRes['err_code_des']];
        }
    }

    /**
     * @param Order $order
     * @param $refundAmount
     * @param $refundType
     * @param int $refundCommissionFee 所退提成费用
     * @return bool
     */
    public function updateOrderRefundInfo(Order $order, $refundAmount, $refundType, $refundCommissionFee = 0)
    {
        // 剩余提成费用
        $commissionFee = $order->commission_fee - $refundCommissionFee;
        // 总共退款费用
        $refundTotal = $order->refund_amount + $refundAmount;
        // 全部退款 单退运费
        switch ($refundType) {
            case RefundBill::REFUND_TYPE_ALL :
                $order->refund_logistics_fee = $order->logistics_fee;
                $order->commission_remain_fee = 0;
                $event = Order::REFUND_EVENT_ALL;
                break;
            case RefundBill::REFUND_TYPE_LOGISTICS_FEE :
                $order->refund_logistics_fee = $order->logistics_fee;
                $event = Order::REFUND_EVENT_LOGISTICS;
                break;
            case RefundBill::REFUND_TYPE_GOODS_FEE :
                // 剩余业绩 = 商品总金额 - 总共退款费用（可能含运费退款） + 被退掉的运费 - 最终的提成费用
                $order->commission_remain_fee = $order->product_amount_total  - $refundTotal + $order->refund_logistics_fee - $commissionFee;
                $event = Order::REFUND_EVENT_GOODS;
                break;
            default :
                break;
        }

        // 订单日志
        $orderService = new OrderService();
        $orderService->saveRefundEventLog($order, $event,'退款：'.$refundAmount.'元');

        $refundMark = Order::REFUND_MARK_PART;
        // 判断是否完全退款
        if ($refundTotal == $order->order_amount_total) {
            $refundMark = Order::REFUND_MARK_ALL;
            $order->status = Order::STATUS_ORDER_CLOSE;
        }
        $order->refund_mark = $refundMark;
        $order->refund_amount = $refundTotal;
        $order->commission_fee = $commissionFee;

        return $order->update();
    }

    public function getOrderGoods($orderNo)
    {
        return OrderGoods::where(['order_no'=>$orderNo])->select('*','product_count as refund_count')->get()->toArray();
    }

    /**
     * 获取需要退款的商品信息
     * @param $goodsArr
     * @param $orderNo
     * @return array
     */
    public function getOrderGoodsFeeInfo($goodsArr,$orderNo)
    {
        $commissionTotal = 0;
        $refundTotal = 0;
        foreach ($goodsArr as $goods) {
            $id = $goods['id'];
            $count = $goods['refund_count'];

            $orderGoods = OrderGoods::where(['order_no'=>$orderNo,'id'=>$id])->first();
            if (!$orderGoods) {
                return ['code' => 1,'msg' => '请选择正确的商品'];
            }

            if ($orderGoods->refund_product_count + $count > $orderGoods->product_count) {
                return ['code' => 1,'msg' => '超过最大可退商品数量'];
            }
            // 商品退费总额
            $commissionTotal += $orderGoods->dist_price * $count;
            $refundTotal += $orderGoods->product_price * $count;
        }
        $data = ['refund_commission_total' => $commissionTotal, 'refund_total' => $refundTotal];
        return ['code' => 0, 'data' => $data];
    }

    /**
     * 更新订单商品表
     * @param $goodsArr
     */
    public function updateOrderGoods($goodsArr)
    {
        foreach ($goodsArr as $goods) {
            $id = $goods['id'];
            $count = $goods['refund_count'];

            $orderGoods = OrderGoods::find($id);
            // 更新订单商品表
            $orderGoods->refund_product_count = $orderGoods->refund_product_count + $count;
            $orderGoods->refund_total_amount = $orderGoods->refund_total_amount + $orderGoods->product_price * $count;

            $orderGoods->update();
        }
        return;
    }

    /**
     * 更新代理订单信息
     * @param $orderNo
     * @param $commission
     * @return bool
     */
    public function updateAgentOrder($orderNo, $commission)
    {
        // 更新分销表
        $agentOrder = AgentOrderMaps::where(['order_no'=>$orderNo])->first();
        if ($agentOrder && $agentOrder->status == AgentOrderMaps::STATUS_DIVIDE_UNSETTLE) {
            // 佣金降为0的时候 处理订单为已结算状态
            if ($agentOrder->commission == $commission) {
                $agentOrder->status = AgentOrderMaps::STATUS_DIVIDE_SETTLED;
            }
            $agentOrder->commission = $agentOrder->commission - $commission;
            return $agentOrder->save();
        }
        return true;
    }

    /**
     * 保存退款记录
     * @param $orderNo
     * @param $refundNo
     * @param $refundType
     * @param $refundAmount
     * @param $refundDesc
     * @return bool
     */
    public function saveRefundBill($orderNo, $refundNo, $refundType, $refundAmount, $refundDesc)
    {
        $refundBill = new RefundBill();

        $refundBill->order_no = $orderNo;
        $refundBill->refund_no = $refundNo;
        $refundBill->refund_type = $refundType;
        $refundBill->refund_amount = $refundAmount;
        $refundBill->refund_desc = $refundDesc;

        $refundBill->save();
        return $refundBill;
    }

    /**
     * 获取退款单号
     * @return string
     */
    private function getRefundNo()
    {
        // 退款单号
        return Order::getOrderNo(Order::PRE_REFUND);
    }

    /**
     * 退款统一接口
     * @param $orderNo
     * @param $refundNumber
     * @param $totalFee
     * @param $refundFee
     * @param $refundDesc
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    private function refund($orderNo, $refundNumber, $totalFee, $refundFee, $refundDesc)
    {
        $app = \EasyWeChat::payment();

        $totalFee = $totalFee * 100;
        $refundFee = $refundFee * 100;
        // 根据商户单号退款
        // 参数分别为：商户订单号、商户退款单号、订单金额、退款金额、其他参数
        return $app->refund->byOutTradeNumber($orderNo, $refundNumber, $totalFee, $refundFee,[
            // 可在此处传入其他参数，详细参数见微信支付文档
            'refund_desc' => $refundDesc,
        ]);
    }

}
