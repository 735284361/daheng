<?php

namespace App\Services;

use App\Models\UserBill;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserBillService
{

    /**
     * 用户账单列表
     * @param $userId
     * @param Request $request
     * @return mixed
     */
    public function lists($userId, Request $request)
    {
        if (!$request->filled('type')) {
            $type = 0;
        } else {
            $type = $request->type;
        }
        $startAt = $request->start_at;
        $endAt = $request->end_at;

        $query = UserBill::where('user_id',$userId);
        // 订单类型
        if ($type != 0) {
            $query->where('bill_type',$type);
        }
        // 订单时间
        if ($startAt && $endAt) {
            $query->whereDate('created_at','>=', $startAt);
            $query->whereDate('created_at','<=', $endAt);
        }
        $list = $query->get();
        $list->map(function ($data) {
           $data->amount_type_str = UserBill::getAmountType($data->amount_type);
           $data->status_str = UserBill::getStatus($data->status);
           $data->bill_type_str = UserBill::getBillType($data->bill_type);
        });
        $data['expand'] = $list->where('amount_type',UserBill::AMOUNT_TYPE_EXPEND)->sum('amount');
        $data['income'] = $list->where('amount_type',UserBill::AMOUNT_TYPE_INCOME)->sum('amount');
        $data['list'] = $list;
        return $data;
    }


    /**
     * 获取当日提现额度
     * @param $userId
     * @return mixed
     */
    public function getTodayWithdrawCount($userId)
    {
        return UserBill::where('user_id',$userId)
            ->where('bill_type',UserBill::BILL_TYPE_WITHDRAW)
            ->whereDate('created_at',Carbon::today())
            ->sum('amount');
    }

    /**
     * 获取当月提现额度
     * @param $userId
     * @return mixed
     */
    public function getMonthWithdrawCount($userId)
    {
        return UserBill::where('user_id',$userId)
            ->where('bill_type',UserBill::BILL_TYPE_WITHDRAW)
            ->whereBetween('created_at',[Carbon::now()->firstOfMonth(),Carbon::now()->lastOfMonth()])
            ->sum('amount');
    }

}
