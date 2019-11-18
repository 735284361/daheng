<?php

namespace App\Services;

use App\Models\ShippingAddress;
use Illuminate\Support\Facades\DB;

class AddressService
{

    /**
     * 保存、修改地址
     * @param $data
     * @return mixed
     */
    public function saveAddress($data)
    {
        if ($data['isDefault'] == 1) {
            ShippingAddress::where('user_id',auth('api')->id())->update(['is_default'=>0]);
        }
        $region = explode(',',$data['region']);
        return ShippingAddress::updateOrCreate([
            'id' => $data['id'],
            'user_id' => auth('api')->id()
        ],[
            'user_id' => auth('api')->id(),
            'name' => $data['name'],
            'phone' => $data['phone'],
            'province' => $region[0],
            'city' => $region[1],
            'county' => $region[2],
            'detail_info' => $data['detailInfo'],
            'postal_code' => $data['postalCode'],
            'is_default' => $data['isDefault'],
        ]);
    }

    public function setDefault($id)
    {
        DB::transaction(function() use($id) {
            ShippingAddress::where('user_id',auth('api')->id())->update(['is_default'=>0]);
            ShippingAddress::where('id',$id)->update(['is_default'=>1]);
        });
        return;
    }

}
