<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AgentMember extends Model
{
    // 代理商下属成员

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->toDateString();
    }

    // 成员的用户信息
    public function user()
    {
        return $this->belongsTo(\App\User::class,'user_id','id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class,'agent_id','user_id');
    }

}
