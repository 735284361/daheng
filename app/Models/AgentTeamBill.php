<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentTeamBill extends Model
{
    //

    const DIVIDE_STATUS_UNDIVIDED = 0; // 未分成
    const DIVIDE_STATUS_DIVIDED = 1; // 已分成

    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(\App\User::class,'user_id','id');
    }

    public static function getDivideStatus($ind = null)
    {
        $arr = [
            self::DIVIDE_STATUS_UNDIVIDED => '未分成',
            self::DIVIDE_STATUS_DIVIDED => '已分成',
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::DIVIDE_STATUS_UNDIVIDED];
        }
        return $arr;
    }
}
