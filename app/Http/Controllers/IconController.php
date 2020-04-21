<?php

namespace App\Http\Controllers;

use App\Models\Icon;
use Illuminate\Http\Request;

class IconController extends Controller
{
    //

    public function lists()
    {
        $list = Icon::where('status',Icon::STATUS_ON)->orderBy('sort','asc')->orderBy('id','asc')->limit(5)->get();
        $list ? $code = 0 : $code = 1;
        return response()->json([
            'code' => $code,
            'data' => $list,
            'msg' => 'success'
        ]);
    }

}
