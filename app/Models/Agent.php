<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    // 代理商用户模型

    const STATUS_APPLY = 0; // 申请
    const STATUS_NORMAL = 1; // 正常
    const STATUS_DISABLE = -1; // 禁用
    const STATUS_REFUSE = -2; // 审核未通过


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
