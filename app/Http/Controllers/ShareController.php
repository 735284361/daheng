<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShareController extends Controller
{
    //
    public function goods()
    {
        //你的业务逻辑代码，获取到相关数据
        return view('share.goods');
    }

}
