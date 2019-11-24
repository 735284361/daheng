<?php

namespace App\Services;

use App\Models\ShippingFee;

class ShippingFeeService
{

    /**
     * 计算运费
     * @param $province
     * @param $totalFee
     * @return int
     */
    public function getShippingFee($province, $totalFee)
    {
        $shippingFee = 0;
        $fee = ShippingFee::where('province',$province)->first();
        if (!$fee) {
            $fee = ShippingFee::where('province','其他')->first();
        }

        if ($totalFee < $fee->full_amount) {
            $shippingFee = $fee->shipping_fee;
        }
        return $shippingFee;
    }

}
