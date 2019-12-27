<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Order\View;
use App\Models\Order;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Illuminate\Support\Arr;

class OrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '订单';

    public function show($id, Content $content)
    {
        $data = Order::find($id);

        $view = view('admin.order.order', compact('data'));
        return $content
            ->title('订单')
            ->description('订单信息...')
            ->row(function (Row $row) use ($view) {
                $row->column(12,function (Column $column) use ($view) {
                    $column->append($view);
                });
            });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order);

        $grid->model()->orderBy('id','desc');

        $grid->column('order_no', __('订单号'));
        $grid->column('user_id', __('用户编号'))->sortable();
        $grid->column('address.name', __('姓名'))->sortable();
        $grid->column('address.phone', __('电话'));
        $grid->column('product_count', __('商品数量'));
        $grid->column('order_amount_total', __('订单金额'))->sortable();
        $grid->column('status', __('订单状态'))->using(Order::getStatus())->label('danger');
        $grid->column('remark', __('备注'));
        $grid->column('created_at', __('下单时间'))->sortable();

        $grid->disableCreateButton();
        $grid->expandFilter();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2,function ($filter) {
                $filter->like('order_no','订单号');
                $filter->like('user_id','用户编号');
                $filter->like('address.name','姓名');
            });

            $filter->column(1/2,function ($filter) {
                $filter->equal('address.phone','手机号');
                $filter->equal('status','状态')->select(Order::getStatus());
                $filter->between('created_at','下单时间')->datetime();
            });
        });

        $grid->actions(function($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
        });

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
        $show = new Show(Order::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('order_no', __('Order no'));
        $show->field('escrow_trade_no', __('Escrow trade no'));
        $show->field('user_id', __('User id'));
        $show->field('product_count', __('Product count'));
        $show->field('product_amount_total', __('Product amount total'));
        $show->field('logistics_fee', __('Logistics fee'));
        $show->field('order_amount_total', __('Order amount total'));
        $show->field('pay_time', __('Pay time'));
        $show->field('delivery_time', __('Delivery time'));
        $show->field('order_settlement_time', __('Order settlement time'));
        $show->field('order_settlement_status', __('Order settlement status'));
        $show->field('after_status', __('After status'));
        $show->field('status', __('Status'));
        $show->field('remark', __('Remark'));
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
        $form = new Form(new Order);

        $form->text('order_no', __('Order no'));
        $form->text('escrow_trade_no', __('Escrow trade no'));
        $form->number('user_id', __('User id'));
        $form->number('product_count', __('Product count'));
        $form->decimal('product_amount_total', __('Product amount total'));
        $form->number('logistics_fee', __('Logistics fee'));
        $form->decimal('order_amount_total', __('Order amount total'));
        $form->datetime('pay_time', __('Pay time'))->default(date('Y-m-d H:i:s'));
        $form->datetime('delivery_time', __('Delivery time'))->default(date('Y-m-d H:i:s'));
        $form->datetime('order_settlement_time', __('Order settlement time'))->default(date('Y-m-d H:i:s'));
        $form->number('order_settlement_status', __('Order settlement status'));
        $form->number('after_status', __('After status'));
        $form->number('status', __('Status'));
        $form->text('remark', __('Remark'));

        return $form;
    }
}
