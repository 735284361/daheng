<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use function EasyWeChat\Kernel\Support\generate_sign;

class PayController extends Controller
{
    //

    protected $payService;

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

        return ['code' => 0,'data' => $payParams];
    }
}
