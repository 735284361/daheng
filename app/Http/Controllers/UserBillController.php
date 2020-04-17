<?php

namespace App\Http\Controllers;

use App\Models\UserBill;
use App\Services\UserBillService;
use Illuminate\Http\Request;

class UserBillController extends Controller
{
    //

    protected $userBillService;

    public function __construct()
    {
        $this->userBillService = new UserBillService();
    }

    public function lists(Request $request)
    {
        return $this->userBillService->lists(auth('api')->id(), $request);
    }

}
