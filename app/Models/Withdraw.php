<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdraw extends Model
{
    //
    protected $table = 'withdraw';

    const STATUS_APPLY = 1; // 提现申请
    const STATUS_PASSED = 2; // 审批通过
    const STATUS_COMPLETED = 3; // 提现成功
    const STATUS_REFUSED = -1; // 审核失败
    const STATUS_WITHDRAW_FAIL = -2; // 提现失败

    // 多态关联账单
    public function bill()
    {
        return $this->morphMany(UserBill::class,'billable');
    }

    public function logs()
    {
        return $this->hasMany(WithdrawLog::class,'withdraw_id','id');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class,'user_id','id');
    }

    public static function getStatus($ids = null) {
        $arr = [
            self::STATUS_APPLY => '申请提现',
            self::STATUS_PASSED => '审批通过',
            self::STATUS_COMPLETED => '提现成功',
            self::STATUS_REFUSED => '审核失败',
            self::STATUS_WITHDRAW_FAIL => '提现失败',
        ];

        if ($ids !== null)  {
            return array_key_exists($ids, $arr) ? $arr[$ids] : $arr[self::STATUS_APPLY];
        }
        return $arr;
    }

}
