<?php

namespace App\Services;

use App\Models\AgentMember;
use App\Models\AgentOrderMaps;
use App\Models\Order;
use App\Models\OrderGoods;

class AgentService
{

    /**
     * 保存订单和代理的关系
     * @param Order $order
     */
    public static function saveAgentOrderMap(Order $order)
    {
        // 代理
        $agent = AgentMember::where('user_id',$order->user_id)->first();
        if ($agent) { // 如果存在代理关系 则进入代理流程
            // 佣金计算
            $orderGoods = OrderGoods::where('order_no',$order->order_no)->get();
            $commission = 0;
            foreach ($orderGoods as $goods) {
                $commission += $goods->product_count * $goods->dist_price;
            }
            // 添加代理订单关系
            $agentOrder = new AgentOrderMaps();
            $agentOrder->agent_id = $agent->agent_id;
            $agentOrder->order_no = $order->order_no;
            $agentOrder->commission = $commission;
            $agentOrder->save();
        }
        return;
    }
}
