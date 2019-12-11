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
}
