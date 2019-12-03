<?php

namespace App\Services;

use App\Jobs\CompleteOrder;
use App\Models\AgentMember;
use App\Models\AgentOrderMaps;
use App\Models\Order;
use App\Models\OrderEventLog;
use App\Models\OrderGoods;
use App\Models\UserBill;
use App\User;
use Illuminate\Support\Facades\DB;

class PayService
{

    public function getPayParams($orderNo, $totalFee, $body = null)
    {
        $body == null ? $body = '原卤大亨' : '';
        $payment = \EasyWeChat::payment();

        $openId = auth('api')->user()->open_id;

        $result = $payment->order->unify([
            'body' => $body,
            'out_trade_no' => $orderNo,
            'total_fee' => $totalFee * 100,
            'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
            'openid' => $openId,
        ]);

        if ($result['return_code'] == 'FAIL' || $result['result_code'] == 'FAIL') {
            $data['code'] = 1;
            $data['result']  = $result;
            return $data;
        } else {
            $jssdk = $payment->jssdk;
            $data['code'] = 0;
            $data['result'] = $jssdk->bridgeConfig($result['prepay_id'],false);
            return $data;
        }
    }


    public function callback()
    {
        $payment = \EasyWeChat::payment();

        $response = $payment->handlePaidNotify(function($message, $fail){
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $order = Order::where('order_no',$message['out_trade_no'])->first();

            if (!$order || $order->status == Order::STATUS_PAID) { // 如果订单不存在 或者 订单已经支付过了
                return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }

            ///////////// <- 建议在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////

            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                if (array_get($message, 'result_code') === 'SUCCESS') {
                    // 用户是否支付成功
                    $order->status = Order::STATUS_PAID;
                } elseif (array_get($message, 'result_code') === 'FAIL') {
                    // 用户支付失败
                    $order->status = Order::STATUS_PAY_FAILED;
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
            $exception = DB::transaction(function() use ($order) {
                // 保存订单
                $order->save();

                // 更新资金流水记录表
                $order->bill()->create([
                    'user_id' => $order->user_id,
                    'amount' => $order->order_amount_total,
                    'amount_type' => UserBill::AMOUNT_TYPE_EXPEND,
                    'status' => UserBill::BILL_STATUS_NORMAL,
                    'bill_type' => UserBill::BILL_TYPE_BUY
                ]);

                // 更新订单日志
                $order->eventLogs()->create([
                    'order_no' => $order->order_no,
                    'event' => OrderEventLog::ORDER_PAID
                ]);

                // 保存订单和代理的关系
                AgentService::saveAgentOrderMap($order);

                // 支付成功 进入消息发送系统
                MessageService::paySuccessMsg($order);

                // 定时结束订单任务
                CompleteOrder::dispatch($order);
            });

            if (!$exception) {
                echo "SUCCESS";
                return true; // 返回处理完成
            } else {
                echo "FAIL";
                return false;
            }
        });
        return $response;
    }

}
