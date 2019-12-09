<?php

namespace App\Utilities;

class Delivery
{
    public function listProviders()
    {
        $app = \EasyWeChat::miniProgram();
        return $app->express->getWaybill();
    }
}
