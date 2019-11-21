<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PayService;
use Illuminate\Http\Request;

class PayController extends Controller
{
    //

    public $payService;

    public function __construct()
    {
        $this->payService = new PayService();
    }

    public function pay()
    {
        $orderNo = date('YmdHis').rand(10000,99999);
        $payParams = $this->payService->getPayParams($orderNo, 1);

        $order = new Order();
        $order->order_no = $orderNo;
        $order->save();

        return ['code' => 0,'data' => $payParams['result']];
    }

    public function refund(Request $request)
    {
        $orderNo = $request->order_no;
        $refundNumber = date('YmdHis').rand(10000,99999);

        $pay = \EasyWeChat::payment();

        return $pay->refund->byOutTradeNumber($orderNo,$refundNumber,1,1);

    }
}
