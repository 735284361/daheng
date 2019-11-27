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
            $data = GoodsSku::where('id',$propertyId)->value('sku');
            return ['stock' => $data->stock, 'price' => $data->price];
        } else {
            $data = Goods::find($goodsId);
            return ['stock' => $data->stock, 'price' => $data->price];
        }
    }

}
