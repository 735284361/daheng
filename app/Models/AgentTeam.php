<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentTeam extends Model
{
    //

    protected $table = 'agent_team';

    protected $guarded = [];

    const STATUS_APPLY = 0; // 申请代理
    const STATUS_NORMAL = 1; // 正常
    const STATUS_DISABLE = -1; // 禁用
    const STATUS_REFUSE = -2; // 审核未通过

    // 队长信息
    public function user_info()
    {
        return $this->hasOne(\App\User::class,'id','user_id');
    }

    // 团队成员
    public function team_users()
    {
        return $this->hasMany(AgentTeamUser::class,'team_id','id');
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

    public static function getStatusDes($ind = null)
    {
        $arr = [
            self::STATUS_APPLY => '信息正在审核中~',
            self::STATUS_NORMAL => '正常',
            self::STATUS_DISABLE => '该团队已被禁止使用',
            self::STATUS_REFUSE => '该团队审核未通过',
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::STATUS_APPLY];
        }
        return $arr;
    }
}
