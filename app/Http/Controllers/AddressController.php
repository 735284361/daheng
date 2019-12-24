<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddresssRequest;
use App\Models\ShippingAddress;
use App\Services\AddressService;
use Illuminate\Http\Request;

class AddressController extends Controller
{

    protected $addressService;

    public function __construct()
    {
        $this->addressService = new AddressService();
    }

    /**
     * 地址列表
     * @return array
     */
    public function lists()
    {
        $list = ShippingAddress::where('status',ShippingAddress::ADDRESS_STATUS_ENABLE)->orderBy('id','desc')->get();
        if ($list) {
            return ['code' => 0,'msg' => 'success','data'=>$list];
        } else {
            return ['code' => 700,'msg' => 'success'];
        }
    }

    /**
     * 地址详情
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function detail(Request $request)
    {
        $this->validate($request,['id'=>'required|integer']);
        $data = ShippingAddress::where('id',$request->id)->where('user_id',auth('api')->id())->first();
        if ($data) {
            $data = json_decode($data, true);
            foreach ($data as $k => $v) {
                $address[camel_case($k)] = $v;
            }
            return ['code' => 0, 'msg' => '获取成功', 'data' => $address];
        } else {
            return ['code' => 1, 'msg' => '获取失败'];
        }
    }

    /**
     * 获取默认地址
     * @param Request $request
     * @return array
     */
    public function default(Request $request)
    {
        $data = ShippingAddress::where('user_id',auth('api')->id())->where('is_default',ShippingAddress::ADDRESS_DEFAULT)->first();
        if ($data) {
            return ['code' => 0, 'msg' => '获取成功', 'data' => $data];
        } else {
            return ['code' => 700, 'msg' => '获取失败'];
        }
    }

    /**
     * 新增或更新地址
     * @param AddresssRequest $request
     * @return array
     */
    public function postAddress(AddresssRequest $request)
    {
        $res = $this->addressService->saveAddress($request->all());
        if ($res) {
            return ['code' => 0, 'msg' => '操作成功'];
        } else {
            return ['code' => 1, 'msg' => '操作失败'];
        }
    }

    /**
     * 软删除地址
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function delete(Request $request)
    {
        $this->validate($request,['id'=>'required|integer']);
        return ShippingAddress::where('id',$request->id)->where('user_id',auth('api')->id())->delete();
    }

    /**
     * 设置默认地址
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setDefault(Request $request)
    {
        $this->validate($request,['id' => 'required|integer']);
        return $this->addressService->setDefault($request->id);
    }

}
