<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SysPic extends Model
{
    //

    public function getPicUrlAttribute($value)
    {
        return Storage::disk(config('filesystems.default'))->url($value);
    }
}
