<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysParams extends Model
{
    //

    // 参数状态
    const PUBLIC_PARAM = 10; // 公开参数
    const PRIVATE_PARAM = 20; // 保密参数

    // 参数类型
    const TXT_PARAM = 1; // 文字参数
    const IMG_PARAM = 2; // 图片参数

    /**
     * 获取参数状态
     * @param null $ind
     * @return array|mixed
     */
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

    /**
     * 获取参数类型
     * @param null $ind
     * @return array|mixed
     */
    public static function getParamsType($ind = null)
    {
        $arr = [
            self::TXT_PARAM => '文字',
            self::IMG_PARAM => '图片'
        ];

        if ($ind !== null) {
            return array_key_exists($ind, $arr) ? $arr[$ind] : $arr[self::TXT_PARAM];
        }

        return $arr;
    }
}
