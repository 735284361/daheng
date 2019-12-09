<?php

namespace App\Services;

use App\Models\Order;

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
            $orderService = new OrderService();
            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                if (array_get($message, 'result_code') === 'SUCCESS') {
                    // 用户是否支付成功
                    return $orderService->paySuccess($order);
                } elseif (array_get($message, 'result_code') === 'FAIL') {
                    // 用户支付失败
                    return $orderService->payFailed($order);
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
        });
        return $response;
    }

}
