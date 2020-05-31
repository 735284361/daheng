<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    //

    use SoftDeletes;

    protected $table = 'order';

    protected $dates = ['delete_at'];

    const STATUS_UNPAID = 0; // 未付款
    const STATUS_PAID = 1; // 已付款
    const STATUS_SHIPPED = 2; // 已发货
    const STATUS_RECEIVED = 3; // 已签收 待评价
    const STATUS_COMPLETED = 4; // 已完成
    const STATUS_PAY_FAILED = -1; // 支付失败
    const STATUS_ORDER_CLOSE = -2; // 订单关闭

    const REFUND_EVENT_ALL = 1; // 全部退款
    const REFUND_EVENT_LOGISTICS = 2; // 运费退款
    const REFUND_EVENT_GOODS = 3; // 部分商品退款

    const EVENT_TYPE_NORMAL = 1; // 正常
    const EVENT_TYPE_REFUND = 2; // 退款

    const PRE_BUY = 'GM'; // 购买订单前缀
    const PRE_REFUND = 'TK'; // 退款订单前缀
    const PRE_WITHDRAW = 'TX'; // 提现

    const UNCOMMENTED = 0; // 未评论
    const COMMENTED = 1; // 已评论

    /**
     * 获取订单号
     * @param $pre
     * @return string
     */
    public static function getOrderNo($pre)
    {
        return $pre.date('YmdHis').rand(10000,99999);
    }

    // 订单的商品列表
    public function goods()
    {
        return $this->belongsToMany(Goods::class,'order_goods','order_no',
            'goods_id','order_no','id')
            ->withPivot('sku', 'product_count','product_price','id','score','comment','refund_product_count','refund_total_amount');
    }

    // 订单地址
    public function address()
    {
        return $this->hasOne(OrderAddress::class,'order_no','order_no');
    }

    // 订单日志
    public function eventLogs()
    {
        return $this->hasMany(OrderEventLog::class,'order_no','order_no')->orderBy('id','desc');
    }

    // 多态关联账单
    public function bill()
    {
        return $this->morphMany(UserBill::class,'billable');
    }

    // 关联用户信息
    public function user()
    {
        return $this->belongsTo(\App\User::class,'user_id','id');
    }

    // 订单对应的代理
    public function orderAgent()
    {
        return $this->hasOne(AgentOrderMaps::class,'order_no','order_no');
    }

    public function agentInfo()
    {
        return $this->hasOneThrough(\App\User::class,AgentOrderMaps::class,'order_no','id','order_no','agent_id');
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

    /**
     * 订单日志
     * @param null $ind
     * @return array|mixed
     */
    public static function getRefundEvents($ind = null)
    {
        $arr = [
            self::REFUND_EVENT_ALL => '全部退款',
            self::REFUND_EVENT_LOGISTICS => '运费退款',
            self::REFUND_EVENT_GOODS => '部分商品退款',
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::REFUND_EVENT_ALL];
        }
        return $arr;
    }

    /**
     * 退款类型
     * @param null $ind
     * @return array|mixed
     */
    public static function getEventType($ind = null)
    {
        $arr = [
            self::EVENT_TYPE_NORMAL => '正常',
            self::EVENT_TYPE_REFUND => '退款',
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::EVENT_TYPE_NORMAL];
        }
        return $arr;
    }
}
