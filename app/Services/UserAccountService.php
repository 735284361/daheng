<?php

namespace App\Services;

use App\Models\UserAccount;

class UserAccountService
{

    public function __construct()
    {
    }

    public function getAccount($userId)
    {
        return UserAccount::where('user_id',$userId)->first();
    }

    /**
     * 增加用户余额
     * @param $userId
     * @param $amount
     * @return mixed
     */
    public function incBalance($userId, $amount)
    {
        return UserAccount::where('user_id',$userId)->increment('balance',$amount);
    }

}
