<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WeChatController extends Controller
{
    //
    public function login(Request $request)
    {
        $code = $request->get('code');
        $nickName = $request->get('nickName');
        $avatarUrl = $request->get('avatarUrl');
        $miniProgram = \EasyWeChat::miniProgram();
        //获取openid session_key
        $data = $miniProgram->auth->session($code);
        //判断code是否过期
        if (isset($data['errcode'])) {
            return response()->json(['code'=>1,'msg'=>'code已过期或不正确']);
        }
        // 获取小程序信息 当前只获取了个昵称及输入的工号密码
        $openId = $data['openid'];
        $sessionKey = $data['session_key'];

//        $openId = 'oXWAQv6dq-KX5ygK8VEqFhTv6go';
//        $sessionKey = 'S8cTQ+DxFFB6BT/UKvV6bA==';

        // 判断子表是否存在该用户
        $user = User::where('open_id',$openId)->first();
        if (!$user) {
            // 该用户不存在
            return response()->json(['code' => '10000']);
        } else {
            // 该用户存在 覆盖用户之前登录信息
            $user->session_key = $sessionKey;
            $user->nickname = $nickName;
            $user->avatar = $avatarUrl;
            $user->save();

            // 用主表Users 创建token
            $createToken = $user->createToken($user['open_id']);
            $createToken->token->save();
            $token = $createToken->accessToken;

            return response()->json([
                'code' => 0,
                'token' => $token,
                'data' => $user,
            ],200);
        }
    }

    public function register(Request $request)
    {
//        $sessionKey = "oyLUVHs3xyPN8arT2JPIsg==";//$request->get('sessionKey');
//        $iv = "CxAkIemE+ff5RldTfjXrTA==";//$request->get('iv');
//        $encryptedData = "r8BGYpPIsBRxuE3hPG799s18C65v2DI5CiZRM6W0KvtKl9Oi3RKRn6XsatlzSeBiTScb04lbms6Tj9oApWofKj4YLTO5oe3OlCYZ2/oU6V7YF5cRHuXNpJJXrQ5f2jUC3OlCVKtcGHimK+fViaNxxWUppWVK06is6xcxgoFD0RL8AT6xDgnzY3iy2k3olqhx4PFBShl0E6mysabOuG3Huk9yxEU+HpEF5NCHt1FDvTSLzZ3Wq8uYYdeD8s+19Q4m+1VigfBMmrOorLRbp2wpwQXY4B1xWw8C88AJ+UXguM1cLSSkfxI+gy7kWwIhb+JHiRACFpn+hhFVUN3NM2IjrY3lCJh+CMPDrg0keFhHc00TaAj/Hfbznq7L7pLpTDdTeu/Nwa5O3vDEvUDe9DqkSqSFs7V6eIqENMeMzTkXi6bg+MakP37N6gQVmKKSmPmzyHNexiZiMZjLfyHyF5ZO5A==";

        $code = $request->get('code');
        $iv = $request->get('iv');
        $encryptedData = $request->get('encryptedData');

        // 解压数据
        $app = \EasyWeChat::miniProgram(); // 小程序
        $sessionData = $app->auth->session($code);
        $sessionKey = $sessionData['session_key'];
        $openId = $sessionData['openid'];

        $data = $app->encryptor->decryptData($sessionKey, $iv, $encryptedData);

        DB::beginTransaction();
        // 查看主库是否存在该用户
        $user = User::firstOrCreate(['open_id'=>$openId],[
            'nickname' => $data['nickName'],
            'open_id' => $data['openId'],
            'avatar' => $data['avatarUrl'],
        ]);
        if ($user) {
            $resAccount = $user->account()->firstOrCreate(['user_id'=>$user->user_id]);
            if ($resAccount) {
                DB::commit();
            } else {
                DB::rollBack();
            }
        }

        return response()->json([
            'code' => 0,
            'msg' => '注册成功'
        ]);
    }

    public function bindPhone(Request $request)
    {
        $code = $request->code;
        $iv = $request->iv;
        $encryptedData = $request->encryptedData;
        $miniProgram = \EasyWeChat::miniProgram();
        $session = $miniProgram->auth->session($code);
        $decryptedData = $miniProgram->encryptor->decryptData($session, $iv, $encryptedData);
        return $decryptedData;
    }

    public function profile()
    {
        return Auth::guard('api')->user();
    }

}
