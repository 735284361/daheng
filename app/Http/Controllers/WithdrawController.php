<?php

namespace App\Http\Controllers;

use App\Http\Requests\WithdrawRequest;
use App\Services\WithdrawService;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
    //

    protected $withdrawService;

    public function __construct()
    {
        $this->withdrawService = new WithdrawService();
    }

    public function apply(WithdrawRequest $request)
    {
        $this->withdrawService->apply($request->apply_total);
        return $this->withdrawService->error();
    }

}
