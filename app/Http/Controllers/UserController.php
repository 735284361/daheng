<?php

namespace App\Http\Controllers;

use App\Services\UserAccountService;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //

    protected $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    /**
     * 获取用户账户余额
     * @return array
     */
    public function getAccount()
    {
        $userAccountService = new UserAccountService(auth('api')->id());
        $account = $userAccountService->getAccount();
        if ($account) {
            return ['code' => 0, 'msg' => 'Success', 'data' => $account];
        } else {
            return ['code' => 1,'msg' => '无数据'];
        }
    }

    /**
     * 意见反馈
     * @param Request $request
     * @return array
     */
    public function feedback(Request $request)
    {
        $this->validate($request,[
            'type' => 'required',
            'content' => 'required|max:500',
            'phone' => 'required|digits:11|integer'
        ]);

        $res = $this->userService->feedback($request);
        if ($res) {
            return ['code' => 0, 'msg' => 'Success'];
        } else {
            return ['code' => 1,'msg' => '失败'];
        }
    }

}
