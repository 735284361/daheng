<?php

namespace App\Admin\Controllers\Api;

use App\Models\Order;
use App\Models\OrderEventLog;
use App\Services\OrderService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    //

    protected $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    /**
     * 修改订单状态
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function updateOrderStatus(Request $request)
    {
        $this->validate($request,['id' => 'required|integer','type' => 'required']);

        $type = $request->type;

        $order = Order::find($request->id);
        switch (strtoupper($type)) {
            case 'SET_PAID' : // 设置为已支付
                $res = $this->orderService->paySuccess($order, 'admin');
                break;
            case 'SET_RECEIVED' : // 设置为确认收货
                $res = $this->orderService->confirmOrder($order, 'admin');
                break;
            case 'SET_CLOSED' : // 订单关闭
                $res = $this->orderService->closeOrder($order, 'admin');
                break;
            case 'SET_COMPLETED' : // 设置为已完成
                $res = $this->orderService->completeOrder($order, 'admin');
                break;
            default :
                $res = false;
                break;
        }

        $res ? $code = 0 : $code = 1;
        return ['code' => $code];
    }

    /**
     * 发货
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function delivery(Request $request)
    {
        $this->validate($request,['id' => 'required|integer','delivery_company' => 'required','delivery_number' => 'required']);

        $order = Order::find($request->id);
        $res = $this->orderService->deliverGoods($order, $request->delivery_company, $request->delivery_number);

        $res ? $code = 0 : $code = 1;
        return ['code' => $code];
    }

    public function refund(Request $request)
    {

    }

}
