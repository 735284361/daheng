<?php

namespace App\Admin\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Utilities\Delivery;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    //

    protected $delivery;

    public function __construct()
    {
        $this->delivery = new Delivery();
    }

    public function listProviders()
    {
        return $this->delivery->listProviders();
    }

}
