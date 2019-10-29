<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    //

    // 产品状态
    const STATUS_ONLINE = 10; // 上架
    const STATUS_OFFLINE = 20; // 下架

    // 商品规格
    public function properties()
    {
        return $this->hasMany(GoodsAttr::class,'goods_id','id')->with('childsCurGoods');
    }

    // 商品规格详细信息
    public function skuArr()
    {
        return $this->hasMany(GoodsSku::class,'goods_id','id');
    }

    public function getSku($id = null)
    {
        if ($id === null) return;
        // 获取该商品下的所有规格属性
        $data = $this->with('properties')->with('skuArr')->where('id',$id)->first()->toArray();
        $skuArr['type'] = $data['sku_type'];
        // 判断规格类型
        if ($data['sku_type'] == 'many') {
            $attrs = [];
            foreach ($data['properties'] as $v) {
                $attrs[$v['name']] = array_column($v['childs_cur_goods'],'name');
            }
            $skuArr['attrs'] = $attrs;
            if (count($data['sku_arr']) > 0) {
                $skuArr['sku'] = array_column($data['sku_arr'],'sku');
            } else {
                $skuArr['sku'] = [];
            }


        }
        return json_encode($skuArr);
    }

    /**
     * 商品状态
     * @param null $ind
     * @return array|mixed
     */
    public static function getStatus($ind = null)
    {
        $arr = [
            self::STATUS_ONLINE => '上架',
            self::STATUS_OFFLINE => '下架',
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::STATUS_OFFLINE];
        }
        return $arr;
    }

}
