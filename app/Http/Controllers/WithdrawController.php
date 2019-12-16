<?php

namespace App\Http\Controllers;

use App\Http\Requests\WithdrawRequest;
use App\Services\WithdrawService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        $this->withdrawService->apply($request->all());
        return $this->withdrawService->error();
    }

}
