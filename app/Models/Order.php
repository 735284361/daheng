<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //

    protected $table = 'order';

    const ORDER_STATUS_PENDING = 10; // 待支付
    const ORDER_STATUS_PAID = 20; // 已支付
    const ORDER_STATUS_FINISH = 30; // 订单已完成
    const ORDER_STATUS_PAID_FAIL = 40; // 已支付

}
