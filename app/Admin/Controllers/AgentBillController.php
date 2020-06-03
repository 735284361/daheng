<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\BtnDivide;
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

        $grid->model()->orderBy('id','desc');

        $grid->header(function ($query) {// 查询出已支付状态的订单总金额
            $total = $query->sum('sales_volume');
            $divideAmount = $query->sum('divide_amount');

            $html = <<<html
                <span class="label label-success">总业绩：<span>$total</span> 元</span>
                <span class="label label-info">总奖金：<span>$divideAmount</span> 元</span>
html;

            return $html;
        });

        $grid->column('id', __('编号'));
        $grid->column('user_id', __('用户编号'))->sortable();
        $grid->column('user_info.nickname', __('用户昵称'))->sortable();
        $grid->column('month', __('月份'))->sortable();
        $grid->column('sales_volume', __('销售业绩'))->sortable();
        $grid->column('divide_amount', __('奖金'))->sortable();
        $grid->column('divide_status', __('分成状态'))->sortable()->using(AgentBill::getDivideStatus())->label([
            AgentBill::DIVIDE_STATUS_UNDIVIDED => 'default',
            AgentBill::DIVIDE_STATUS_DIVIDED => 'success',
        ]);
        $grid->column('created_at', __('创建时间'))->sortable();
//        $grid->column('updated_at', __('Updated at'));

        $grid->disableCreateButton();
        $grid->expandFilter();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2,function ($filter) {
                $filter->equal('user_id','用户编号');
                $filter->like('user_info.nickname','用户昵称');
                $filter->equal('month','日期');
            });

            $filter->column(1/2,function ($filter) {
                $filter->equal('divide_status','分成状态')->select(AgentBill::getDivideStatus());
                $filter->group('sales_volume', '销售额', function ($group) {
                    $group->gt('大于');
                    $group->lt('小于');
                    $group->nlt('不小于');
                    $group->ngt('不大于');
                    $group->equal('等于');
                });
                $filter->group('divide_amount', '奖金', function ($group) {
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
