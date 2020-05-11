<?php

namespace App\Services;

use App\Models\Order;
use App\Models\UserAccount;
use App\Models\UserBill;
use App\Models\Withdraw;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WithdrawService
{

    public $errors = ['code' => 0,'msg' => 'success'];

    protected $payService;
    protected $withdraw;

    public function __construct()
    {
        $this->payService = new PayService();
    }

    public function error()
    {
        return $this->errors;
    }

    /**
     * 申请提现
     * @param $applyTotal
     * @return bool
     * @throws \Throwable
     */
    public function apply($applyTotal)
    {
        $account = UserAccount::where('user_id',auth('api')->id())->first();

        // 判断账户余额
        if ($account->balance < $applyTotal) {
            $this->errors = ['code' => 1,'msg' => '账户余额不足'];
            return false;
        }

        if ($this->getRemainApplyCount() <= 0) {
            $this->errors = ['code' => 1,'msg' => '提现额度已用完'];
            return false;
        } else if ($this->getRemainApplyCount() < $applyTotal) {
            $this->errors = ['code' => 1,'msg' => '提现额度超过限额'];
            return false;
        }

        $exception = DB::transaction(function () use($applyTotal) {
            $orderNo = Order::getOrderNo(Order::PRE_WITHDRAW);
            // 添加提现记录
            $withdraw = new Withdraw();
            $withdraw->user_id = auth('api')->id();
            $withdraw->withdraw_order = $orderNo;
            $withdraw->apply_total = $applyTotal;
            $withdraw->save();

            $this->withdraw = $withdraw;
            // 添加提现日志
            $this->saveWithdrawLog(Withdraw::STATUS_APPLY);

            // 减少用户账户余额 新增提现中的余额
            $userAccountService = new UserAccountService();
            $userAccountService->applyWithdraw($applyTotal);

            // 更新资金流水记录表
            UserBillService::saveBillInfo($this->withdraw,auth('api')->id(), '提现申请', $applyTotal, UserBill::AMOUNT_TYPE_EXPEND,
                UserBill::BILL_STATUS_WAITING_INCOME, UserBill::BILL_TYPE_WITHDRAW);

            // 短信提醒
            AdminMsgService::sendWithdrawApplyMsg();
        });
        if (!is_null($exception)) {
            $this->errors = ['code' => 1,'msg' => '提现失败'];
        }
        return is_null($exception) ? true : false;
    }

    /**
     * 获取用户当日提现剩余额度
     * @return int|mixed|string
     */
    private function getRemainApplyCount()
    {
        // 判断当日已申请提现的额度
        $applyDayCount = Withdraw::where('user_id',auth('api')->id())->whereDate('created_at',Carbon::today())->sum('apply_total');

        // 判断当月已申请提现的额度
        $applyMonthCount = Withdraw::where('user_id',auth('api')->id())
            ->whereDate('created_at','>=',Carbon::now()->firstOfMonth())
            ->whereDate('created_at','<=',Carbon::now()->lastOfMonth())
            ->sum('apply_total');

        // 如果当日和当月还有剩余额度 则返回最小的额度
        $remainCount = 0;
        if (env('WITHDRAW_TODAY_LIMIT') > $applyDayCount && env('WITHDRAW_THIS_MONTH_LIMIT') > $applyMonthCount) {
            $dayRemain = env('WITHDRAW_TODAY_LIMIT') - $applyDayCount;
            $monthRemain = env('WITHDRAW_THIS_MONTH_LIMIT') - $applyMonthCount;
            $remainCount = $dayRemain >= $monthRemain ?  $monthRemain : $dayRemain;
        }
        return $remainCount;
    }

    /**
     * 同意提现申请
     * @param $id
     * @param $remark
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function agreeWithdrawApply($id, $remark)
    {
        $this->withdraw = Withdraw::find($id);
        if (!$this->withdraw) {
            return ['code' => 1, 'msg' => '未查询到订单信息'];
        }

        if ($this->withdraw->status != Withdraw::STATUS_APPLY) {
            return ['code' => 1, 'msg' => '该订单不支持提现'];
        }

        $exception = DB::transaction(function () use ($remark) {
            // 更新提现日志
            $this->updateWithdrawStatus(Withdraw::STATUS_COMPLETED);
            // 添加提现日志
            $this->saveWithdrawLog(Withdraw::STATUS_COMPLETED, $remark);
            // 处理提现中的余额
            $userAccountService = new UserAccountService($this->withdraw->user_id);
            $userAccountService->agreeWithdraw($this->withdraw->apply_total);

            // 账单
            $userBill = $this->withdraw->bill()->first();
            UserBillService::updateBillStatus($userBill,UserBill::BILL_STATUS_NORMAL);
        });
        if (!$exception) {
            return ['code' => 0, 'msg' => '成功'];
        } else {
            return ['code' => 1, 'msg' => '失败'];
        }
//        // 查询提现状态
//        $queryData = $this->payService->queryBalanceOrder($this->withdraw->withdraw_order);
//        if ($queryData['return_code'] == 'SUCCESS' && $queryData['result_code'] == 'FAIL') {
//            $transData =  $this->payService->transferToBalance($this->withdraw->withdraw_order, $this->withdraw->apply_total, $this->withdraw->user_id, $remark);
//            if ($transData['return_code'] == 'SUCCESS') {
//                if ($transData['result_code'] == 'SUCCESS') {
//                    $exception = DB::transaction(function () use ($remark) {
//                        // 更新提现日志
//                        $this->updateWithdrawStatus(Withdraw::STATUS_COMPLETED);
//                        // 添加提现日志
//                        $this->saveWithdrawLog(Withdraw::STATUS_COMPLETED, $remark);
//                        // 处理提现中的余额
//                        $userAccountService = new UserAccountService($this->withdraw->user_id);
//                        $userAccountService->agreeWithdraw($this->withdraw->apply_total);
//                    });
//                    if (!$exception) {
//                        return ['code' => 0, 'msg' => '成功'];
//                    } else {
//                        return ['code' => 1, 'msg' => '失败'];
//                    }
//                } else {
//                    return ['code' => 1, 'msg' => $transData['err_code'].":".$transData['err_code_des']];
//                }
//            } else {
//                $this->withdraw->status = Withdraw::STATUS_COMPLETED;
//                return ['code' => 1, 'msg' => $transData['err_code'].":".$transData['err_code_des']];
//            }
//        } else {
//            return ['code' => 1, 'msg' => '已打款'];
//        }
    }

    /**
     * 拒绝提现申请
     * @param $id
     * @param $remark
     * @return array
     * @throws \Throwable
     */
    public function refuseWithdrawApply($id, $remark)
    {
        $this->withdraw = Withdraw::find($id);
        if (!$this->withdraw) {
            return ['code' => 1, 'msg' => '未查询到订单信息'];
        }

        if ($this->withdraw->status != Withdraw::STATUS_APPLY) {
            return ['code' => 1, 'msg' => '该订单不支持提现'];
        }
        // 处理提现中的余额
        $exception = DB::transaction(function () use ($remark) {
            // 更新提现日志
            $this->updateWithdrawStatus(Withdraw::STATUS_REFUSED);
            // 添加提现日志
            $this->saveWithdrawLog(Withdraw::STATUS_REFUSED, $remark);
            // 处理提现中的余额
            $userAccountService = new UserAccountService($this->withdraw->user_id);
            $userAccountService->refuseWithdraw($this->withdraw->apply_total);

            // 更新账单
            $userBill = $this->withdraw->bill()->first();
            UserBillService::updateBillStatus($userBill,UserBill::BILL_STATUS_REFUSED);
        });
        if (!$exception) {
            return ['code' => 0, 'msg' => '成功'];
        } else {
            return ['code' => 1, 'msg' => '失败'];
        }
    }

    /**
     * 更新提现状态
     * @param $status
     * @return mixed
     */
    private function updateWithdrawStatus($status)
    {
        $this->withdraw->status = $status;
        return $this->withdraw->save();
    }

    /**
     * 保存提现日志
     * @param $status
     * @param $remark
     * @return mixed
     */
    private function saveWithdrawLog($status, $remark = null)
    {
        return $this->withdraw->logs()->create([
            'remark' => $remark,
            'status' => $status
        ]);
    }

}
