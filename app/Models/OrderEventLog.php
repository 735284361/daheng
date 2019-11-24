<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderEventLog extends Model
{
    // 订单状态变化日志

    protected $guarded = [];

    const ORDER_CREATED = 0; // 创建订单
    const ORDER_PAID = 1; // 支付完成
    const ORDER_SIPPED = 2; // 已发货
    const ORDER_RECEIVED = 3; // 已签收
    const ORDER_COMPLETED = 4; // 已完成
    const ORDER_PAY_FAILED = -1; // 支付失败

    /**
     * 获取订单事件名称
     * @param null $ind
     * @return array|mixed
     */
    public static function getEvents($ind = null)
    {
        $arr = [
            self::ORDER_CREATED => '创建订单',
            self::ORDER_PAID => '支付完成',
            self::ORDER_SIPPED => '已发货',
            self::ORDER_RECEIVED => '已签收',
            self::ORDER_COMPLETED => '已完成',
            self::ORDER_PAY_FAILED => '支付失败',
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::ORDER_CREATE];
        }
        return $arr;
    }
}
