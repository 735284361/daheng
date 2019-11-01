<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //

    const ORDER_STATUS_PENDING = 10; // 待支付
    const ORDER_STATUS_PAID = 20; // 已支付
    const ORDER_STATUS_PAID_FAIL = 30; // 已支付

}
