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
    const STATUS_RECEIVED = 3; // 已签收 待评价
    const STATUS_COMPLETED = 4; // 已完成
    const STATUS_PAY_FAILED = -1; // 支付失败
    const STATUS_ORDER_CLOSE = -2; // 订单关闭

    const PRE_BUY = 'GM'; // 购买订单前缀
    const PRE_REFUND = 'TK'; // 退款订单前缀
    const PRE_WITHDRAW = 'TX'; // 提现

    /**
     * 获取订单号
     * @param $pre
     * @return string
     */
    public function getOrderNo($pre)
    {
        return $pre.date('YmdHis').rand(10000,99999);
    }

    // 订单的商品列表
    public function goods()
    {
        return $this->belongsToMany(Goods::class,'order_goods','order_no',
            'goods_id','order_no','id')->withPivot('sku', 'product_count','product_price');
    }

    // 订单地址
    public function address()
    {
        return $this->hasOne(OrderAddress::class,'order_no','order_no');
    }

    // 订单日志
    public function eventLogs()
    {
        return $this->hasMany(OrderEventLog::class,'order_no','order_no');
    }

    // 多态关联账单
    public function bill()
    {
        return $this->morphMany(UserBill::class,'billable');
    }

    /**
     * 获取轮播图的状态
     * @param null $ind
     * @return array|mixed
     */
    public static function getStatus($ind = null)
    {
        $arr = [
            self::STATUS_UNPAID => '未付款',
            self::STATUS_PAID => '已付款',
            self::STATUS_SHIPPED => '已发货',
            self::STATUS_RECEIVED => '已签收',
            self::STATUS_COMPLETED => '已完成',
            self::STATUS_PAY_FAILED => '支付失败',
            self::STATUS_ORDER_CLOSE => '订单关闭',
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::STATUS_UNPAID];
        }
        return $arr;
    }
}
