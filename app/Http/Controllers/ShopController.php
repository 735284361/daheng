<?php

namespace App\Http\Controllers;

use App\Models\Goods;
use App\Models\GoodsSku;
use App\Models\ShippingFee;
use App\Services\GoodsService;
use App\Services\ShippingFeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ShopController extends Controller
{
    //

    protected $goodsService;

    public function __construct()
    {
        $this->goodsService = new GoodsService();
    }

    // 获取商品列表
    public function lists(Request $request)
    {
        $categoryId = $request->input('categoryId',"");
        $recommend = $request->input('recommend',false);
        $categoryId == "" ? '' : $maps['category_id'] = $categoryId;
        $recommend ? $maps['recommend_status'] = Goods::RECOMMEND_STATUS_YES : '';
        $maps['status'] = Goods::STATUS_ONLINE;
        $data =  Goods::where($maps)->orderBy('sort','asc')->orderBy('number_score','desc')->get();
        if ($data) {
            $arr = ['code' => 0,'msg' => 'success' ,'data' => $data];
        } else {
            $arr = ['code' => 700,'msg' => '暂无数据'];
        }
        return $arr;
    }

    // 获取商品详细信息
    public function detail(Request $request)
    {
        $id = $request->id;
        $data = Goods::where('id',$id)
            ->with('properties')
            ->with('skuArr')
            ->with('reputation')
            ->with('content')
            ->first();
        if ($data) {
            $data = json_decode($data,true);
            if (empty($data['properties']) || $data['sku_type'] == 'single') {
                $data['properties'] = null;
            }
            $data['sku_arr'] = array_column($data['sku_arr'],'sku');

            $pics = $data['pics'];
            $pics = array_map(function($value) {
                return getStorageUrl($value);
            },$pics);
            $data['pics'] = $pics;
        }
        // 增加商品浏览量
        $this->goodsService->incViewsCount($id);
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
        $shippingFeeService = new ShippingFeeService();
        $fee = $shippingFeeService->getShippingFee($request->province, $request->total_fee);
        return ['code' => 0,'msg' => 'success','data' => $fee];
    }

    public function getGoodsStock(Request $request)
    {
        $this->validate($request,['goods_id'=>'required|integer','property_id'=>'nullable|integer']);
        // 商品库存信息
        $stock = $this->goodsService->getSku($request->goods_id,$request->property_id);
        // 商品状态
        $goods = Goods::find($request->goods_id);
        $isOnline = false;
        if ($goods && $goods->status == Goods::STATUS_ONLINE) {
            $isOnline = true;
        }
        $stock->is_online = $isOnline;

        return ['code' => 0, 'msg' => 'Success', 'data' => $stock];
    }
}
