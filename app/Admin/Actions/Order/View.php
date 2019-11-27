<?php

namespace App\Admin\Actions\Order;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class View extends RowAction
{
    public $name = '查看';

    public function handle(Model $model)
    {
        // $model ...

        return $this->response()->success('Success message.')->refresh();
    }

    public function href()
    {
        $href = parent::href();
        $key = $this->getKey();
        return "/admin/orders/show";
    }

}
