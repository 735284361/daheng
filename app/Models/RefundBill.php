<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundBill extends Model
{
    //

    const REFUND_TYPE_ALL = 1; // 订单退款
    const REFUND_TYPE_LOGISTICS_FEE = 2; // 运费退款
    const REFUND_TYPE_GOODS_FEE = 3; // 商品退款

    // 多态关联账单
    public function bill()
    {
        return $this->morphMany(UserBill::class,'billable');
    }

    public static function getRefundType($ind = null)
    {
        $arr = [
            self::REFUND_TYPE_ALL => '订单退款',
            self::REFUND_TYPE_LOGISTICS_FEE => '运费退款',
            self::REFUND_TYPE_GOODS_FEE => '商品退款',
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::REFUND_TYPE_ALL];
        }
        return $arr;
    }
}
