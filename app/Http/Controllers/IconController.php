<?php

namespace App\Http\Controllers;

use App\Models\GoodsCategory;
use App\Models\Icon;
use Illuminate\Http\Request;

class IconController extends Controller
{
    //

    public function lists()
    {
        $list = Icon::where('status',Icon::STATUS_ON)
            ->whereHas('category', function($query) {
                $query->where('status', GoodsCategory::STATUS_ENABLE);
            })
            ->orderBy('sort','asc')
            ->orderBy('id','asc')
            ->limit(10)
            ->get();
        $list ? $code = 0 : $code = 1;
        return response()->json([
            'code' => $code,
            'data' => $list,
            'msg' => 'success'
        ]);
    }

}
