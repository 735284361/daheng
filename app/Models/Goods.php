<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Goods extends Model
{
    //

    // 产品状态
    const STATUS_ONLINE = 10; // 上架
    const STATUS_OFFLINE = 20; // 下架

    // 推荐状态
    const RECOMMEND_STATUS_YES = 10; // 推荐
    const RECOMMEND_STATUS_NO = 20; // 普通

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

    // 商品介绍
    public function content()
    {
        return $this->hasOne(GoodsContent::class,'goods_id','id');
    }

    // 商品分类
    public function category()
    {
        return $this->belongsTo(GoodsCategory::class,'category_id','id');
    }

    // 商品评论
    public function reputation()
    {
        return $this->hasMany(OrderGoods::class,'goods_id','id')
            ->whereNotNull('comment')->with('user');
    }

    // 获取-商品列表图
    public function getPicUrlAttribute($value)
    {
        return Storage::disk(config('filesystems.default'))->url($value);
    }

    // 获取-商品详情轮播
    public function getPicsAttribute($value)
    {
        $list = json_decode($value,true);
        $list = array_map(function($value) {
            return Storage::disk(config('filesystems.default'))->url($value);
        },$list);
        return $list;
    }

    // 设置-商品详情轮播
    public function setPicsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['pics'] = json_encode($value);
        }
    }

    // 获取商品规格
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

    /**
     * 获取商品推荐属性
     * @param null $ind
     * @return array|mixed
     */
    public static function getRecommendStatus($ind = null)
    {
        $arr = [
            self::RECOMMEND_STATUS_YES => '推荐',
            self::RECOMMEND_STATUS_NO => '普通',
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::RECOMMEND_STATUS_NO];
        }
        return $arr;
    }

}
