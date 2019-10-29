<?php

namespace App\Http\Controllers;

use App\Models\Goods;
use App\Models\GoodsSku;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    //

    // 获取商品列表
    public function lists()
    {
        return Goods::where('status',Goods::STATUS_ONLINE)->get();
    }

    // 获取商品详细信息
    public function detail(Request $request)
    {
        $id = $request->id;
        $data = Goods::where('id',$id)->with('properties')->with('skuArr')->first()->toArray();
        $data['sku_arr'] = array_column($data['sku_arr'],'sku');
        return response()->json($data);
    }

    // 获取规格价格
    public function price(Request $request)
    {
        $id = $request->id;
        $attr = json_decode($request->attrs,true);

        foreach ($attr as $k=>$v) {
            $map['sku->'.$k] = $v;
        }
        //  测试123
        return GoodsSku::where($map)->where('goods_id',$id)->first();
    }
}
