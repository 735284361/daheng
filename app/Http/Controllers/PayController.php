<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PayController extends Controller
{
    //

    public function pay()
    {
        $app = \EasyWeChat::payment();

        $openId = 'opjVL5GvAS-NsWVFQ6CxaOtpXAMU';// Auth::user()->open_id;

        $result = $app->order->unify([
            'body' => '原卤大亨',
            'out_trade_no' => date('YMDHis').rand(10000,99999),
            'total_fee' => 101,
            'spbill_create_ip' => '', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
            'notify_url' => 'http://dh.raohouhai.com/pay/callback', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
            'openid' => $openId,
        ]);
        $result['time_stamp'] = (string)time();
        $data = ['code' => 0,'data' => $result];
        return $data;
    }

    public function callback(Request $request)
    {
        Log::warning('start');
        Log::warning($request);
    }
}
