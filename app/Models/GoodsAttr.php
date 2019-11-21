<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsAttr extends Model
{
    //

    protected $table = 'goods_attr';


//    protected $fillable = ['goods_id','name'];

    protected $guarded = ['id'];

    public function properties()
    {
        return $this->belongsTo(Goods::class,'id','goods_is');
    }

    // 规格的二级规格
    public function childsCurGoods()
    {
        return $this->hasMany(GoodsAttrValue::class,'goods_attr_id','id');
    }

}
