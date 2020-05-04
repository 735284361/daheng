<?php

namespace App\Admin\Controllers;

use App\Models\AgentBill;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AgentBillController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '代理结算';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AgentBill);

        $grid->column('id', __('Id'));
        $grid->column('user_id', __('User id'));
        $grid->column('month', __('Month'));
        $grid->column('sales_volume', __('Sales volume'));
        $grid->column('divide_status', __('Divide status'));
        $grid->column('divide_amount', __('Divide amount'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(AgentBill::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('month', __('Month'));
        $show->field('sales_volume', __('Sales volume'));
        $show->field('divide_status', __('Divide status'));
        $show->field('divide_amount', __('Divide amount'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AgentBill);

        $form->number('user_id', __('User id'));
        $form->text('month', __('Month'));
        $form->number('sales_volume', __('Sales volume'));
        $form->number('divide_status', __('Divide status'));
        $form->number('divide_amount', __('Divide amount'));

        return $form;
    }
}
