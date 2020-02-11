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

    public static function getStatus($ind = null)
    {
        $arr = [
            self::BILL_STATUS_NORMAL => '正常',
            self::BILL_STATUS_WAITING_INCOME => '入账中',
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::BILL_STATUS_NORMAL];
        }
        return $arr;
    }

    public static function getAmountType($ind = null)
    {
        $arr = [
            self::AMOUNT_TYPE_EXPEND => '支出',
            self::AMOUNT_TYPE_INCOME => '收入',
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::AMOUNT_TYPE_EXPEND];
        }
        return $arr;
    }

    public static function getBillType($ind = null)
    {
        $arr = [
            self::BILL_TYPE_BUY => '购物消费',
            self::BILL_TYPE_RECHARGE => '现金充值',
            self::BILL_TYPE_WITHDRAW => '用户提现',
            self::BILL_TYPE_COMMISSION => '订单佣金',
            self::BILL_TYPE_DIVIDE => '销售分成'
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::BILL_TYPE_BUY];
        }
        return $arr;
    }
}
