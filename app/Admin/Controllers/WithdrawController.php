<?php

namespace App\Admin\Controllers;

use App\Models\Withdraw;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;

class WithdrawController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '提现管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Withdraw);

        $grid->model()->orderBy('id','desc');

        $grid->header(function ($query) {// 查询出已支付状态的订单总金额
            $total = $query->sum('apply_total');

            $html = <<<html
                <span class="label label-success">总额：<span>$total</span> 元</span>
html;

            return $html;
        });

        $grid->column('id', __('编号'));
        $grid->column('user_id', __('用户编号'))->sortable();
        $grid->column('user.nickname', __('用户昵称'));
        $grid->column('withdraw_order', __('订单号'));
        $grid->column('apply_total', __('金额'))->sortable();
        $grid->column('status', __('状态'))->using(Withdraw::getStatus())->label([
            Withdraw::STATUS_APPLY => 'default',
            Withdraw::STATUS_PASSED => 'success',
            Withdraw::STATUS_COMPLETED => 'info',
            Withdraw::STATUS_REFUSED => 'primary',
            Withdraw::STATUS_WITHDRAW_FAIL => 'warning'
        ])->sortable();
        $grid->column('created_at', __('申请时间'))->sortable();
        $grid->column('updated_at', __('更新时间'))->sortable();

        $grid->disableCreateButton();

        $grid->expandFilter();

        $grid->actions(function ($gird) {
           $gird->disableDelete();
           $gird->disableEdit();
        });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2,function ($filter) {
                $filter->like('user_id','用户编号');
                $filter->like('user.name','用户昵称');
            });

            $filter->column(1/2,function ($filter) {
                $filter->equal('status','状态')->select(Withdraw::getStatus());
                $filter->between('created_at','申请时间')->datetime();
            });
        });

        return $grid;
    }

    public function show($id, Content $content)
    {
        $data = Withdraw::find($id);

        $view = view('admin.withdraw.withdraw',compact('data'));

        return $content
            ->title($this->title)
            ->description('详情...')
            ->row(function (Row $row) use ($view) {
                $row->column(12,function (Column $column) use ($view) {
                    $column->append($view);
                });
            });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Withdraw::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_Id', __('User Id'));
        $show->field('withdraw_order', __('Withdraw order'));
        $show->field('apply_total', __('Apply total'));
        $show->field('status', __('Status'));
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
        $form = new Form(new Withdraw);

        $form->number('user_Id', __('User Id'));
        $form->text('withdraw_order', __('Withdraw order'));
        $form->decimal('apply_total', __('Apply total'));
        $form->switch('status', __('Status'))->default(10);

        return $form;
    }
}
