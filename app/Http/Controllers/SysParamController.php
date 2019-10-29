<?php

namespace App\Http\Controllers;

use App\Models\SysParams;
use Illuminate\Http\Request;

class SysParamController extends Controller
{
    //

    // 获取系统参数
    public function value(Request $request)
    {
        $key = $request->key;
        $data = SysParams::where('code',$key)->first();
        $data ? $code = 0 : $code = 1;

        return response()->json([
            'code' => $code,
            'data' => $data,
            'msg' => 'success'
        ]);
    }
}
