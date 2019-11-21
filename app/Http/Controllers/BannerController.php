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
        $list = Banner::where('status',Banner::STATUS_ONLINE)->orderBy('sort','asc')->get();
        $list ? $code = 0 : $code = 1;
        return response()->json([
            'code' => $code,
            'data' => $list,
            'msg' => 'success'
        ]);
    }

}
