<?php

namespace App\Admin\Controllers;

use App\Models\ShippingFee;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ShippingFeeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\ShippingFee';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ShippingFee);

        $grid->column('id', __('ID'));
        $grid->column('province', __('省份'));
        $grid->column('shipping_fee', __('基础运费'));
        $grid->column('full_amount', __('满减金额'));
        $grid->column('created_at', __('创建时间'));
        $grid->column('updated_at', __('修改时间'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(ShippingFee::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('province', __('省份'));
        $show->field('shipping_fee', __('基础运费'));
        $show->field('full_amount', __('满减金额'));
        $show->field('created_at', __('创建时间'));
        $show->field('updated_at', __('更新时间'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ShippingFee);

        $form->text('province', __('省份'))->required();
        $form->number('shipping_fee', __('基础运费'))->required();
        $form->number('full_amount', __('满减金额'))->required();

        return $form;
    }
}
