<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use App\Models\OrderGoods;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class OrderGoodsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商品统计';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OrderGoods);

        $grid->model()
            ->whereHas('orders',function($query) {
                $query->whereIn('status',[
                    Order::STATUS_PAID,
                ]);
            })
            ->groupBy(['goods_id','sku'])
            ->orderBy('goods_id','asc')
            ->select([
                '*',DB::raw("sum(product_count - refund_product_count) as total_count")
            ]);

//        $grid->header(function ($query) {// 查询出已支付状态的订单总金额
//            $total = $query->sum('product_count');
//            $total2 = $query->sum('refund_product_count');
//
//            $html = <<<html
//                <span class="label label-success">总数：<span>$total</span> 份</span>
//                <span class="label label-info">总退款数：<span>$total2</span> 份</span>
//html;
//
//            return $html;
//        });

//        $grid->column('order_no', __('订单号'));
//        $grid->column('user_id', __('用户编号'));
        $grid->column('goods.name', __('商品'))->sortable();
        $grid->column('goods_id', __('商品编号'))->sortable();
        $grid->column('sku', __('规格'));
        $grid->column('total_count', __('数量'))->sortable();
//        $grid->column('product_price', __('单价'));
//        $grid->column('dist_price', __('分成价格'));
//        $grid->column('comment', __('评论'));
//        $grid->column('refund_product_count', __('退款数量'));
//        $grid->column('refund_total_amount', __('退款总额'));
//        $grid->column('created_at', __('创建时间'));

        $grid->disableCreateButton();
        $grid->expandFilter();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2,function ($filter) {
                $filter->like('goods_id','商品编号');
            });

            $filter->column(1/2,function ($filter) {
                $filter->like('goods.name','商品名称');
            });
        });

        $grid->disableActions();

//        $grid->perPage = 100;
        // 禁用分页
        $grid->disablePagination();
//        $grid->disableBatchActions();

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
        $show = new Show(OrderGoods::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('order_no', __('Order no'));
        $show->field('user_id', __('User id'));
        $show->field('goods_id', __('Goods id'));
        $show->field('sku', __('Sku'));
        $show->field('property_id', __('Property id'));
        $show->field('product_count', __('Product count'));
        $show->field('product_price', __('Product price'));
        $show->field('dist_price', __('Dist price'));
        $show->field('score', __('Score'));
        $show->field('comment', __('Comment'));
        $show->field('refund_product_count', __('Refund product count'));
        $show->field('refund_total_amount', __('Refund total amount'));
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
        $form = new Form(new OrderGoods);

        $form->text('order_no', __('Order no'));
        $form->number('user_id', __('User id'));
        $form->text('goods_id', __('Goods id'));
        $form->text('sku', __('Sku'));
        $form->number('property_id', __('Property id'));
        $form->number('product_count', __('Product count'));
        $form->decimal('product_price', __('Product price'));
        $form->decimal('dist_price', __('Dist price'))->default(0.00);
        $form->number('score', __('Score'));
        $form->text('comment', __('Comment'));
        $form->number('refund_product_count', __('Refund product count'));
        $form->decimal('refund_total_amount', __('Refund total amount'))->default(0.00);

        return $form;
    }
}
