<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    // 代理商用户模型

    protected $fillable = ['user_id'];

    public function members()
    {
        $this->hasMany(AgentMember::class,'agent_id','id');
    }

    // 多态关联账单
    public function bill()
    {
        return $this->morphMany(UserBill::class,'billable');
    }
}
