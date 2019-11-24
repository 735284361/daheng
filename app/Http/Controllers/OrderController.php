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

    public function create(OrderRequest $request)
    {
        return $this->orderService->create($request);
    }

}
