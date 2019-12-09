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

    public function statistics()
    {
        $data = $this->orderService->statistics();
        if ($data) {
            return ['code' => 0, 'data' => $data];
        } else {
            return ['code' => 1];
        }
    }
}
