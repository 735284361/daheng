<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentMember extends Model
{
    // 代理商下属成员

    public function user()
    {
        return $this->belongsTo(\App\User::class,'user_id','id');
    }

}