<?php

namespace App\Http\Controllers;

use App\Models\GoodsCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    //

    public function lists()
    {
        $list = GoodsCategory::where('status',GoodsCategory::STATUS_ENABLE)->orderby('sort','asc')->orderby('id','asc')->get();

        if ($list) return ['code' => 0, 'data' => $list, 'msg' => 'success'];
        else return ['code' => 1, 'data' => [], 'msg' => 'false'];
    }

}
