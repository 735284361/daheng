<?php

namespace App\Services;

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

}
