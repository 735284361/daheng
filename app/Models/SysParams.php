<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysParams extends Model
{
    //

    const PUBLIC_PARAM = 10; // 公开参数
    const PRIVATE_PARAM = 20; // 保密参数

    public static function getStatus($ind = null)
    {
        $arr = [
            self::PUBLIC_PARAM => '公开参数',
            self::PRIVATE_PARAM => '保密参数'
        ];

        if ($ind !== null) {
            return array_key_exists($ind, $arr) ? $arr[$ind] : $arr[self::PUBLIC_PARAM];
        }

        return $arr;
    }
}
