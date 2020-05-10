<?php

namespace App\Services;

use App\Models\Feedback;
use App\User;
use Illuminate\Http\Request;

class UserService
{

    public function feedback(Request $request)
    {
        $feedback = new Feedback();
        $feedback->user_id = auth('api')->id();
        $feedback->type = $request->type;
        $feedback->content = $request->input('content');
        $feedback->phone = $request->phone;
        return $feedback->save();
    }

    public static function bindPhone($phone)
    {
        $user = auth('api')->user();

        $isExists = User::where('id','!=',$user->id)->where('phone','=',$phone)->exists();
        if ($isExists) {
            return ['code' => 1,'msg' => '该手机号已被使用'];
        }

        $user->phone = $phone;
        $user->update();

        return ['code' => 0,'msg' => '绑定成功'];
    }

}
