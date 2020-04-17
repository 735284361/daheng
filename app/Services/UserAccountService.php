<?php

namespace App\Services;

use App\Models\UserAccount;

class UserAccountService
{

    protected $userAccount;
    protected $account;

    public function __construct($userId = null)
    {
        $userId == null ? $userId = auth('api')->id() : '';
        $this->userAccount = UserAccount::firstOrCreate(['user_id'=>$userId]);
    }

    /**
     * 获取账户余额
     * @return mixed
     */
    public function getAccount()
    {
        return $this->userAccount;
    }

    /**
     * 申请提现
     * @param $amount
     */
    public function applyWithdraw($amount)
    {
        // 减少 账户余额
        $this->decBalance($amount);
        // 增加 提现中 余额
        $this->incCashIn($amount);
        return;
    }

    /**
     * 同意提现
     * @param $amount
     */
    public function agreeWithdraw($amount)
    {
        // 减少 提现中 的余额
        $this->decCashIn($amount);
        // 增加 已提现 的余额
        $this->incAccountWithdrawn($amount);
        return;
    }

    /**
     * 拒绝提现
     * @param $amount
     */
    public function refuseWithdraw($amount)
    {
        // 减少 提现中 的余额
        $this->decCashIn($amount);
        // 增加 余额
        $this->incBalance($amount);
        return;
    }

    /**
     * 增加 用户余额
     * @param $amount
     * @return mixed
     */
    public function incBalance($amount)
    {
        return $this->userAccount->increment('balance',$amount);
    }

    /**
     * 减少 账户余额
     * @param $amount
     * @return mixed
     */
    public function decBalance($amount)
    {
        return $this->userAccount->decrement('balance',$amount);
    }

    /**
     * 增加 已提现 余额
     * @param $amount
     * @return mixed
     */
    public function incAccountWithdrawn($amount)
    {
        return $this->userAccount->increment('withdrawn',$amount);
    }

    /**
     * 减少 已提现 余额
     * @param $amount
     * @return mixed
     */
    public function decAccountWithdrawn($amount)
    {
        return $this->userAccount->decrement('withdrawn',$amount);
    }

    /**
     * 增加 提现中 余额
     * @param $amount
     * @return mixed
     */
    public function incCashIn($amount)
    {
        return $this->userAccount->increment('cash_in',$amount);
    }

    /**
     * 减少 提现中 余额
     * @param $amount
     * @return mixed
     */
    public function decCashIn($amount)
    {
        return $this->userAccount->decrement('cash_in',$amount);
    }

}
