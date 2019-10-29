<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    //

    // 获取轮播列表
    public function getList()
    {
        $statusOnline = Banner::STATUS_ONLINE;
        $list = Banner::where('status',$statusOnline)->get();
        $list ? $code = 0 : $code = 1;

        return response()->json([
            'code' => $code,
            'data' => $list,
            'msg' => 'success'
        ]);
    }

}
