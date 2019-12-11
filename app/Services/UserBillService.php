<?php

namespace App\Services;

use App\Models\UserBill;
use Illuminate\Http\Request;

class UserBillService
{

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
           $data->status_str = UserBill::getAmountType($data->status);
           $data->bill_type_str = UserBill::getBillType($data->bill_type);
        });
        $data['expand'] = $list->where('amount_type',UserBill::AMOUNT_TYPE_EXPEND)->sum('amount');
        $data['income'] = $list->where('amount_type',UserBill::AMOUNT_TYPE_INCOME)->sum('amount');
        $data['list'] = $list;
        return $data;
    }

}
