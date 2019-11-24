<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //

    protected $table = 'order';

    const STATUS_UNPAID = 0; // 未付款
    const STATUS_PAID = 1; // 已付款
    const STATUS_SHIPPED = 2; // 已发货
    const STATUS_RECEIVED = 3; // 已签收
    const STATUS_COMPLETED = 4; // 已完成

    const PRE_BUY = 'GM'; // 购买订单前缀
    const PRE_REFUND = 'TK'; // 退款订单前缀
    const PRE_WITHDRAW = 'TX'; // 提现


    public function getOrderNo($pre)
    {
        return $pre.date('YmdHis').rand(10000,99999);
    }

}
