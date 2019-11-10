<?php

namespace App\Services;

class PayService
{

    const CALLBACK_SINCE_PAY_GOODS = 'payGoods';

    public function pay()
    {
        $orderNo = date('YmdHis').rand(10000,99999);
        $payParams = $this->getPayParams($orderNo,1);

        $order = new Order();
        $order->order_no = $orderNo;

        $order->save();

        return ['code' => 0,'data' => $payParams];
    }

    private function getPayParams($body, $orderNo, $totalFee, $notifyUrl)
    {
        $payment = \EasyWeChat::payment();

        $openId = auth('api')->user()->open_id;

        $attach = [];
        $result = $payment->order->unify([
            'body' => $body,
            'out_trade_no' => $orderNo,
            'total_fee' => $totalFee,
            'notify_url' => $notifyUrl,
            'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
            'openid' => $openId,
            'attach' => $attach
        ]);

        $jssdk = $payment->jssdk;

        return $jssdk->bridgeConfig($result['prepay_id'],false);
    }

    public function callback(Request $request)
    {
        Log::warning('start');
        $payment = \EasyWeChat::payment();

        $response = $payment->handlePaidNotify(function($message, $fail){
//            array (
//                'appid' => 'wx16711afd0c23cc36',
//                'bank_type' => 'CFT',
//                'cash_fee' => '1',
//                'fee_type' => 'CNY',
//                'is_subscribe' => 'N',
//                'mch_id' => '1561162401',
//                'nonce_str' => '5dbbc130dcf25',
//                'openid' => 'opjVL5GvAS-NsWVFQ6CxaOtpXAMU',
//                'out_trade_no' => '2019110105225643223',
//                'result_code' => 'SUCCESS',
//                'return_code' => 'SUCCESS',
//                'sign' => '0E2AFC94958F4086526D41F0F8A65FDE',
//                'time_end' => '20191101132309',
//                'total_fee' => '1',
//                'trade_type' => 'JSAPI',
//                'transaction_id' => '4200000449201911015497249599',
//            );

            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $order = Order::where('order_no',$message['out_trade_no'])->first();

            if (!$order || $order->status == Order::ORDER_STATUS_PAID) { // 如果订单不存在 或者 订单已经支付过了
                return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }

            ///////////// <- 建议在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////

            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                // 用户是否支付成功
                if (array_get($message, 'result_code') === 'SUCCESS') {
                    $order->status = Order::ORDER_STATUS_PAID;
                    // 用户支付失败
                } elseif (array_get($message, 'result_code') === 'FAIL') {
                    $order->status = Order::ORDER_STATUS_PAID;
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }

            $order->save(); // 保存订单

            return true; // 返回处理完成
        });

        Log::warning('finish');
        return $response;
    }

    // 退款
    public function refund(Request $request)
    {
        $orderNo = $request->order_no;
        $refundNo = 'TK'.date('YmdHis').rand(10000,99999);;

        $order = Order::where('order_no',$orderNo)->first()->toArray();

        $payment = \EasyWeChat::payment();
        $result = $payment->refund->byOutTradeNumber($orderNo, $refundNo, 1, 1, [
            // 可在此处传入其他参数，详细参数见微信支付文档
            'refund_desc' => '退运费',
        ]);
        return $result;
    }

}
