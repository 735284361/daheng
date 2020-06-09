<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderGoods extends Model
{
    // 订单商品

    public function goods()
    {
        return $this->belongsTo(Goods::class,'goods_id','id');
    }

    // 关联用户信息
    public function user()
    {
        return $this->belongsTo(\App\User::class,'user_id','id')->select('id','nickname','avatar');
    }

    public function orders()
    {
        return $this->belongsTo(Order::class,'order_no','order_no');
    }
}
