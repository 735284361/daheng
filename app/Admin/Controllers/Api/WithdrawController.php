<?php

namespace App\Admin\Controllers\Api;

use App\Services\WithdrawService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WithdrawController extends Controller
{
    //

    protected $withdrawService;
    protected $withdraw;

    public function __construct()
    {
        $this->withdrawService = new WithdrawService();
    }

    public function agreeWithdraw(Request $request)
    {
        $this->validate($request,['id'=>'required|integer']);
        return $this->withdrawService->agreeWithdrawApply($request->id, $request->remark);
    }

    public function refuseWithdraw(Request $request)
    {
        $this->validate($request,['id'=>'required|integer']);
        return $this->withdrawService->refuseWithdrawApply($request->id, $request->remark);
    }

}
