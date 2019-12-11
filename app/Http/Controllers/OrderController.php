<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    //

    protected $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    /**
     * 生成订单
     * @param OrderRequest $request
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(OrderRequest $request)
    {
        return $this->orderService->create($request);
    }

    /**
     * 订单状态统计
     * @return array
     */
    public function statistics()
    {
        $data = $this->orderService->statistics();
        if ($data) {
            return ['code' => 0, 'data' => $data];
        } else {
            return ['code' => 1];
        }
    }

    /**
     * 订单列表
     * @return array
     */
    public function lists()
    {
        $list = $this->orderService->orderList();
        if ($list) {
            return ['code' => 0, 'data' => $list];
        } else {
            return ['code' => 1];
        }
    }

    /**
     * 订单详情
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function detail(Request $request)
    {
        $this->validate($request,['id'=>'required|integer']);
        $list = $this->orderService->orderDetail($request->id);
        if ($list) {
            return ['code' => 0, 'data' => $list];
        } else {
            return ['code' => 1];
        }
    }

    public function reputation(Request $request)
    {
        $res = $this->orderService->reputation($request);
        if ($res) {
            return ['code' => 0, 'msg' => '成功'];
        } else {
            return ['code' => 1, 'msg' => '失败'];
        }
    }

}
