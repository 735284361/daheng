<?php

namespace App\Services;

use App\Models\Goods;
use App\Models\GoodsSku;

class GoodsService
{

    /**
     * 获取商品规格
     * @param $goodsId
     * @param $propertyId
     * @return mixed
     */
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

    /**
     * 增加商品浏览量
     * @param $goodsId
     * @return mixed
     */
    public function incViewsCount($goodsId)
    {
        return Goods::where('id',$goodsId)->increment('number_views');
    }

    /**
     * 处理订单数据
     * @param $goodsId
     * @param $count
     * @param int $propertyId
     */
    public function dealGoodsCount($goodsId, $count, $propertyId = 0)
    {
        // 处理商品库存
        $goods = Goods::find($goodsId);
        if ($propertyId == 0) {
            $goods->stock = $goods->stock - $count;
        } else {
            GoodsSku::where('id',$propertyId)->decrement('sku->stock',$count);
        }
        // 处理商品销量
        $goods->number_sells = $goods->number_sells + $count;
        // 处理商品订单数
        $goods->number_orders = $goods->number_orders + 1;
        $goods->save();
        return;
    }

}
