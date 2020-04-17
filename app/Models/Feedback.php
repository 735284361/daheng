<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    //
    const TYPE_DEFAULT = 0;
    const TYPE_GOODS = 1;
    const TYPE_SHIPPING = 2;
    const TYPE_SERVICE = 3;
    const TYPE_FUNCTION = 4;
    const TYPE_PRODUCT = 5;
    const TYPE_OTHER = 6;


    public function user()
    {
        return $this->belongsTo(\App\User::class,'user_id','id');
    }


    public static function getTypes($ind = null)
    {
        $arr = [
            self::TYPE_DEFAULT => '请选择',
            self::TYPE_GOODS => '商品相关',
            self::TYPE_SHIPPING => '物流状况',
            self::TYPE_SERVICE => '客户服务',
            self::TYPE_FUNCTION => '功能异常',
            self::TYPE_PRODUCT => '产品建议',
            self::TYPE_OTHER => '其他',
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::TYPE_GOODS];
        }
        return $arr;
    }
}
