<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsSku extends Model
{
    //
    protected $table = 'goods_sku';

    protected $guarded = [];

    // 将sku json字段解析为数组
    public function getSkuAttribute($value)
    {
        return json_decode($value);
    }

    // 将sku 数组转换为json类型
    public function setSkuAttribute($value)
    {
        $this->attributes['sku'] = json_encode($value);
    }

}
