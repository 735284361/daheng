<?php

namespace App;

use App\Models\AgentMember;
use App\Models\UserAccount;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Overtrue\EasySms\PhoneNumber;

class User extends Authenticatable
{
    use HasApiTokens,Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//    protected $fillable = [
//        'name', 'email', 'password',
//    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'email'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function routeNotificationForEasySms($notification)
    {
        return new PhoneNumber($this->number, $this->area_code);
    }

    protected $guarded=[];

    public function agent()
    {
        return $this->belongsTo(AgentMember::class,'id','user_id');
    }

    public function account()
    {
        return $this->hasOne(UserAccount::class,'user_id','id');
    }
}
