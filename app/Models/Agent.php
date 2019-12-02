<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    // 代理商用户模型

    public function members()
    {
        $this->hasMany(AgentMember::class,'agent_id','id');
    }

}
