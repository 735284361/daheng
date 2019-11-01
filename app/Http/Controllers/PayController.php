<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use function EasyWeChat\Kernel\Support\generate_sign;

class PayController extends Controller
{
    //

    public function pay()
    {
        $orderNo = date('YmdHis').rand(10000,99999);
        $payParams = $this->getPayParams($orderNo,1);

        $order = new Order();
        $order->order_no = $orderNo;

        $order->save();

        return ['code' => 0,'data' => $payParams];
    }

    private function getPayParams($orderNo, $totalFee)
    {
        $payment = \EasyWeChat::payment();

        $openId = auth('api')->user()->open_id;

        $result = $payment->order->unify([
            'body' => '原卤大亨',
            'out_trade_no' => $orderNo,
            'total_fee' => $totalFee,
//            'spbill_create_ip' => '', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
//            'notify_url' => 'http://dh.raohouhai.com/pay/callback', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
            'openid' => $openId,
        ]);

        $jssdk = $payment->jssdk;

        return $jssdk->bridgeConfig($result['prepay_id'],false);
    }

    public function callback(Request $request)
    {
        Log::warning('start');
        $payment = \EasyWeChat::payment();

        $response = $payment->handlePaidNotify(function($message, $fail){
            Log::warning('continue');
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $order = Order::where('order_no',$message['out_trade_no'])->find();

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
}
