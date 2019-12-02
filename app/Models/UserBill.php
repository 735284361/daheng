<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBill extends Model
{
    // 用户账单

    // 消费类型
    const AMOUNT_TYPE_EXPEND = -1; // 支出
    const AMOUNT_TYPE_INCOME = 1; // 收入

    // 消费状态
    const BILL_STATUS_NORMAL = 1; // 正常
    const BILL_STATUS_WAITING_INCOME = 2; // 入账中

    // 账单分类
    const BILL_TYPE_BUY = 1; // 购物
    const BILL_TYPE_RECHARGE = 2; // 充值
    const BILL_TYPE_WITHDRAW = 3; // 提现
    const BILL_TYPE_COMMISSION = 4; // 佣金
    const BILL_TYPE_DIVIDE = 5; // 分成

    protected $guarded = [];

    public function billable()
    {
        return $this->morphTo();
    }

}
