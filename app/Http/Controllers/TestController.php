<?php

namespace App\Http\Controllers;

use App\Jobs\CompleteOrder;
use App\Models\AgentMember;
use App\Models\AgentOrderMaps;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Services\MessageService;
use App\User;
use Illuminate\Http\Request;

class TestController extends Controller
{
    //

    public function test()
    {
        // 支付成功消息发送
//        $order = Order::find(10);
//        return MessageService::paySuccessMsg($order);

        // 用户账单
//        $order = Order::where('order_no','GM2019112614502267154')->first();
//        $order->bill()->create([
//            'user_id' => 8,
//            'amount' => 100,
//            'amount_type' => -1,
//            'status' => 1,
//        ]);

        // 用户代理商判断
//        $user = AgentMember::where('user_id',1)->first();

//        $agent = AgentMember::where('user_id',8)->first();
//        if ($agent) { // 如果存在代理关系 则进入代理流程
//            // 佣金计算
//            $orderGoods = OrderGoods::where('order_no','GM2019112614502267154')->get();
//            $commission = 0;
//            foreach ($orderGoods as $goods) {
//                $commission += $goods->product_count * $goods->dist_price;
//            }
//            // 添加代理订单关系
//            $agentOrder = new AgentOrderMaps();
//            $agentOrder->agent_id = $agent->agent_id;
//            $agentOrder->order_no = 'GM2019112614502267154';
//            $agentOrder->commission = $commission;
//            $agentOrder->save();
//        }

        $order = Order::find(11);
        CompleteOrder::dispatch($order);
    }

}
