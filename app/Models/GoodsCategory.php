<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsCategory extends Model
{
    //

    protected $table = 'goods_category';

    // 分类状态
    const STATUS_ENABLE = 10; // 正常
    const STATUS_DISABLE = 20; // 禁用

    public static function getStatus($ind = null)
    {
        $arr = [
            self::STATUS_ENABLE => '正常',
            self::STATUS_DISABLE => '禁用',
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::STATUS_ENABLE];
        }
        return $arr;
    }
}
