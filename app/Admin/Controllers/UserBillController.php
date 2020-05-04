<?php

namespace App\Admin\Controllers;

use App\Models\UserBill;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserBillController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户资金流水';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new UserBill);

        $grid->model()->orderBy('id','desc');

        $grid->column('id', __('编号'));
        $grid->column('user_id', __('用户编号'))->sortable();
        $grid->column('bill_name', __('账单'))->sortable();
        $grid->column('amount', __('金额'))->sortable();
        $grid->column('amount_type', __('收入类型'))->using(UserBill::getAmountType())->label([
            UserBill::AMOUNT_TYPE_INCOME => 'success',
            UserBill::AMOUNT_TYPE_EXPEND => 'warning',
        ])->sortable();
        $grid->column('status', __('账单状态'))->using(UserBill::getStatus())->label([
            UserBill::BILL_STATUS_NORMAL => 'default',
            UserBill::BILL_STATUS_WAITING_INCOME => 'warning',
        ])->sortable();
        $grid->column('bill_type', __('账单类型'))->using(UserBill::getBillType())->label([
            UserBill::BILL_TYPE_BUY => 'success',
            UserBill::BILL_TYPE_RECHARGE => 'primary',
            UserBill::BILL_TYPE_WITHDRAW => 'warning',
            UserBill::BILL_TYPE_COMMISSION => 'danger',
            UserBill::BILL_TYPE_DIVIDE => 'default',
        ])->sortable();
        $grid->column('created_at', __('创建日期'))->sortable();
        $grid->column('updated_at', __('更新日期'))->sortable();

        $grid->disableCreateButton();
        $grid->expandFilter();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2,function ($filter) {
                $filter->equal('user_id','用户编号');
                $filter->equal('amount_type','收入类型')->select(UserBill::getAmountType());
                $filter->group('amount', '金额', function ($group) {
                    $group->gt('大于');
                    $group->lt('小于');
                    $group->nlt('不小于');
                    $group->ngt('不大于');
                    $group->equal('等于');
                });
            });

            $filter->column(1/2,function ($filter) {
                $filter->equal('bill_type','账单类型')->select(UserBill::getBillType());
                $filter->equal('status','账单状态')->select(UserBill::getStatus());;
                $filter->between('created_at','账单时间')->datetime();
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
        $show = new Show(UserBill::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('bill_name', __('Bill name'));
        $show->field('amount', __('Amount'));
        $show->field('amount_type', __('Amount type'));
        $show->field('status', __('Status'));
        $show->field('bill_type', __('Bill type'));
        $show->field('billable_id', __('Billable id'));
        $show->field('billable_type', __('Billable type'));
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
        $form = new Form(new UserBill);

        $form->number('user_id', __('User id'));
        $form->text('bill_name', __('Bill name'));
        $form->decimal('amount', __('Amount'));
        $form->number('amount_type', __('Amount type'));
        $form->number('status', __('Status'));
        $form->number('bill_type', __('Bill type'));
        $form->number('billable_id', __('Billable id'));
        $form->text('billable_type', __('Billable type'));

        return $form;
    }
}
