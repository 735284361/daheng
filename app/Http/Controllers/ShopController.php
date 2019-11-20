<?php

namespace App\Http\Controllers;

use App\Models\Goods;
use App\Models\GoodsSku;
use App\Models\ShippingFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    //

    // 获取商品列表
    public function lists(Request $request)
    {
        $categoryId = $request->input('categoryId',"");
        $categoryId == "" ? '' : $maps['category_id'] = $categoryId;
        $maps['status'] = Goods::STATUS_ONLINE;
        $perPage = $request->input('per_page',100);
        $data =  Goods::where($maps)->paginate($perPage)->toArray();
        if ($data && count($data) > 0) {
            $arr = ['code' => 0,'msg' => 'success' ,'data' => $data['data']];
        } else {
            $arr = ['code' => 700,'msg' => '暂无数据'];
        }
        return $arr;
    }

    // 获取商品详细信息
    public function detail(Request $request)
    {
        $id = $request->id;
        $data = Goods::where('id',$id)->with('properties')->with('skuArr')
            ->with('reputation')->with('content')->first()->toArray();
        $data['sku_arr'] = array_column($data['sku_arr'],'sku');
        return response()->json($data);
    }

    /**
     * 获取商品指定规格的数据
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function price(Request $request)
    {
        $this->validate($request,['id'=>'required','attrs'=>'required','attrs.*'=>'required']);
        $id = $request->id;
        $attr = json_decode($request->attrs,true);

        foreach ($attr as $k=>$v) {
            $map['sku->'.$k] = $v;
        }
        return GoodsSku::where($map)->where('goods_id',$id)->first();
    }

    /**
     * 获取指定省份的运费模板
     * @param Request $request
     * @return array
     */
    public function shippingFee(Request $request)
    {
        $province = $request->province;
        if ($fee = ShippingFee::where('province',$province)->first()) {
            return ['code' => 0,'msg' => 'success','data' => $fee];
        } else {
            $fee = ShippingFee::where('province','其他')->first();
            return ['code' => 0,'msg' => 'success','data' => $fee];
        }
    }
}
