<?php

namespace App\Services;

use App\Models\UserAccount;

class UserAccountService
{

    /**
     * 增加用户余额
     * @param $userId
     * @param $amount
     * @return mixed
     */
    public function incBalance($userId, $amount)
    {
        $userAccount = UserAccount::updateOrCreate(['user_id' => $userId]);
        return $userAccount->increment('balance',$amount);
    }

}
