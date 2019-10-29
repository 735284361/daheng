<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    //

    const STATUS_ONLINE = 10;
    const STATUS_OFFLINE = 20;

    /**
     * 获取轮播图的状态
     * @param null $ind
     * @return array|mixed
     */
    public static function getStatus($ind = null)
    {
        $arr = [
            self::STATUS_ONLINE => '启用',
            self::STATUS_OFFLINE => '禁用',
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::STATUS_OFFLINE];
        }
        return $arr;
    }
}
