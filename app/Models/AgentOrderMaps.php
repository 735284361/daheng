<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentOrderMaps extends Model
{
    // 代理商与下属成员订单的关系

    // 佣金结算状态
    const STATUS_UNSETTLE = 0; // 未结算
    const STATUS_SETTLED = 1; // 已结算
    const STATUS_CANCEL = -1; // 已取消

    // 分成结算状态
    const STATUS_DIVIDE_UNSETTLE = 0; // 未结算
    const STATUS_DIVIDE_SETTLED = 1; // 未结算
    const STATUS_DIVIDE_CANCEL = -1; // 已取消

    // 对应的订单信息
    public function order()
    {
        return $this->belongsTo(Order::class,'order_no','order_no');
    }

    // 对应的代理商信息
    public function agent()
    {
        return $this->belongsTo(Agent::class,'agent_id','user_id');
    }
}
