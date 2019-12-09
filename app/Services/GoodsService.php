<?php

namespace App\Services;

use App\Models\Goods;
use App\Models\GoodsSku;

class GoodsService
{

    public function getSku($goodsId, $propertyId)
    {
        // 如果有属性ID 则查询对应的属性信息
        // 如果没有属性ID 则直接查询商品信息
        if ($propertyId) {
            return GoodsSku::where('id',$propertyId)->value('sku');
        } else {
            return Goods::find($goodsId);
        }
    }

}
