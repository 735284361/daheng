<?php

namespace App\Http\Controllers;

use App\Services\UserAccountService;
use App\Services\UserService;
use Encore\Admin\Form\Field\Id;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //

    protected $userService;
    protected $userAccountService;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->userAccountService = new UserAccountService();
    }

    public function getAccount()
    {
        $account = $this->userAccountService->getAccount(auth('api')->id());
        if ($account) {
            return ['code' => 0, 'msg' => 'Success', 'data' => $account];
        } else {
            return ['code' => 1,'msg' => '无数据'];
        }
    }

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
