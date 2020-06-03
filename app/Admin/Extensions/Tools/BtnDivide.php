<?php
namespace App\Admin\Extensions\Tools;

use Carbon\Carbon;
use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class BtnDivide extends AbstractTool
{
    public function render()
    {
        $month = getSubMonth();
        return view('admin.tools.btn-divide',compact('month'));
    }
}
