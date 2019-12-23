<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawLog extends Model
{
    //

    protected $table = 'withdraw_logs';

    protected $guarded = [];

    public function withdraw()
    {
        return $this->belongsTo(Withdraw::class,'withdraw_id','id');
    }

    public function getStatusAttribute($value)
    {
        return Withdraw::getStatus($value);
    }

}
