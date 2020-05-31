<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderEventLog extends Model
{
    // 订单状态变化日志

    protected $guarded = [];

    /**
     * 将事件的编号自动转化为对应的文字提示
     * @param $value
     * @return array|mixed
     */
//    public function getEventAttribute($value)
//    {
//        return Order::getStatus($value);
//    }
}
