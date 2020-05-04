<?php

namespace App\Admin\Controllers;

use App\Models\AgentTeamBill;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AgentTeamBillController extends AdminController
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
        $grid = new Grid(new AgentTeamBill);

        $grid->model()->orderBy('id','desc');

        $grid->column('id', __('编号'))->sortable();
//        $grid->column('team_id', __('Team id'));
        $grid->column('user_id', __('用户编号'))->sortable();
        $grid->column('users.nickname', __('昵称'))->sortable();
        $grid->column('month', __('日期'))->sortable();
        $grid->column('sales_volume', __('销售额'))->sortable();
        $grid->column('divide_status', __('分成状态'))->using(AgentTeamBill::getDivideStatus())->label([
            AgentTeamBill::DIVIDE_STATUS_DIVIDED => 'success',
            AgentTeamBill::DIVIDE_STATUS_UNDIVIDED => 'warning',
        ])->sortable();
        $grid->column('divide_total_amount', __('团队总奖金'))->sortable();
        $grid->column('divide_remain_amount', __('团队队长奖金'))->sortable();
        $grid->column('created_at', __('创建时间'));
        $grid->column('updated_at', __('更新时间'));

        $grid->disableCreateButton();
        $grid->expandFilter();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2,function ($filter) {
                $filter->equal('user_id','用户编号');
                $filter->equal('month','日期');
                $filter->equal('divide_status','分成状态')->select(AgentTeamBill::getDivideStatus());
            });

            $filter->column(1/2,function ($filter) {
                $filter->group('sales_volume', '销售额', function ($group) {
                    $group->gt('大于');
                    $group->lt('小于');
                    $group->nlt('不小于');
                    $group->ngt('不大于');
                    $group->equal('等于');
                });
                $filter->group('divide_total_amount', '总奖金', function ($group) {
                    $group->gt('大于');
                    $group->lt('小于');
                    $group->nlt('不小于');
                    $group->ngt('不大于');
                    $group->equal('等于');
                });
                $filter->group('divide_remain_amount', '队长奖金', function ($group) {
                    $group->gt('大于');
                    $group->lt('小于');
                    $group->nlt('不小于');
                    $group->ngt('不大于');
                    $group->equal('等于');
                });
            });
        });
        $grid->disableActions();
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
        $show = new Show(AgentTeamBill::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('team_id', __('Team id'));
        $show->field('user_id', __('User id'));
        $show->field('month', __('Month'));
        $show->field('sales_volume', __('Sales volume'));
        $show->field('divide_status', __('Divide status'));
        $show->field('divide_total_amount', __('Divide total amount'));
        $show->field('divide_remain_amount', __('Divide remain amount'));
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
        $form = new Form(new AgentTeamBill);

        $form->number('team_id', __('Team id'));
        $form->number('user_id', __('User id'));
        $form->text('month', __('Month'));
        $form->number('sales_volume', __('Sales volume'));
        $form->number('divide_status', __('Divide status'));
        $form->number('divide_total_amount', __('Divide total amount'));
        $form->number('divide_remain_amount', __('Divide remain amount'));

        return $form;
    }
}
