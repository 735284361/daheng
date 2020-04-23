<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Icon extends Model
{
    //

    const STATUS_ON = 0; // 在线
    const STATUS_OFF = 1; // 下线

    public function category()
    {
        return $this->belongsTo(GoodsCategory::class,'category_id','id');
    }

    public function getIconImgAttribute($value)
    {
        return Storage::disk(config('filesystems.default'))->url($value);
    }


    public static function getStatus($ind = null)
    {
        $arr = [
            self::STATUS_ON => '在线',
            self::STATUS_OFF => '下线',
        ];

        if ($ind !== null) {
            return array_key_exists($ind,$arr) ? $arr[$ind] : $arr[self::STATUS_ON];
        }
        return $arr;
    }
}
