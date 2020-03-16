<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AgentTeamUser extends Model
{
    //

    protected $guarded = [];

    public function team()
    {
        return $this->belongsTo(AgentTeam::class,'team_id','user_id');
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class,'user_id','user_id');
    }

    // 成员对应的用户信息
    public function user_info()
    {
        return $this->belongsTo(\App\User::class,'user_id','id');
    }

    // 成员对应的账单
    public function agent_bill()
    {
        return $this->hasMany(AgentBill::class,'user_id','user_id');
    }

    public function agent_month_bill()
    {
        return $this->belongsTo(AgentBill::class,'user_id','user_id');
    }

    // 时间
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->toDateString();
    }
}
