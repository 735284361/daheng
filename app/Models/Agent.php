<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    // 代理商用户模型

    const STATUS_APPLY = 0; // 申请代理
    const STATUS_NORMAL = 1; // 正常
    const STATUS_DISABLE = -1; // 禁用
    const STATUS_REFUSE = -2; // 审核未通过


    protected $fillable = ['user_id'];

    // 代理的成员
    public function members()
    {
        return $this->hasMany(AgentMember::class,'agent_id','user_id');
    }

    // 多态关联账单
    public function bill()
    {
        return $this->morphMany(UserBill::class,'billable');
    }

    // 代理商的基本信息
    public function user()
    {
        return $this->belongsTo(\App\User::class,'user_id','id');
    }

    // 代理商成员的消费订单
    public function agentMembersOrders()
    {
        return $this->hasMany(AgentOrderMaps::class,'agent_id','user_id');
    }

    public static function getStatus($ind = null)
    {
        $arr = [
            self::STATUS_APPLY => '申请代理',
            self::STATUS_NORMAL => '正常',
            self::STATUS_DISABLE => '禁用',
            self::STATUS_REFUSE => '审核未通过',
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::STATUS_APPLY];
        }
        return $arr;
    }
}
