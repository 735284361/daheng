<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingAddress extends Model
{
    //
    use SoftDeletes;

    protected $table = 'shipping_address';
    protected $dates = ['deleted_at'];
    protected $guarded = [];


    const ADDRESS_STATUS_ENABLE = 10; // 正常
    const ADDRESS_STATUS_DISABLE = 20; // 禁用

}
