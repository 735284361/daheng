<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\OrdersService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CompleteOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        //
//        $deley = 10 * 24 * 3600; // 10天后自动完成订单
        $deley = 1;
        $this->order = $order;
        $this->delay($deley);
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // 订单结束
        // 只有订单状态为已支付 才进行订单关闭的操作
        if ($this->order->status != Order::STATUS_PAID) {
            return;
        }
        // 进入订单完成流程
        $orderService = new OrdersService();
        $orderService->completeOrder($this->order);
        return;
    }
}
