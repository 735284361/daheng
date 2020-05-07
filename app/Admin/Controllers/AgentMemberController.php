<?php

namespace App\Admin\Controllers;

use App\Models\AgentMember;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AgentMemberController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '顾客管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AgentMember);

        $grid->model()->orderBy('id','desc');

        $grid->column('id', __('编号'))->sortable();
        $grid->column('agentInfo.avatar', __('代理头像'))->image('',100,100);
        $grid->column('agentInfo.nickname', __('顾客昵称'));
        $grid->column('agent_id', __('代理商编号'))->sortable();
        $grid->column('user.avatar', __('顾客头像'))->image('',100,100);
        $grid->column('user.nickname', __('顾客昵称'))->sortable();
        $grid->column('user_id', __('顾客编号'))->sortable();
        $grid->column('amount', __('消费额'))->sortable();
        $grid->column('order_number', __('订单量'))->sortable();
        $grid->column('created_at', __('创建时间'))->sortable();

        $grid->disableCreateButton();
        $grid->expandFilter();
        $grid->actions(function ($gird) {
            $gird->disableEdit();
            $gird->disableView();
        });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2,function ($filter) {
                $filter->like('user_id','用户编号');
                $filter->like('agent_id','代理编号');
                $filter->like('agentInfo.nickname','代理昵称');
            });

            $filter->column(1/2,function ($filter) {
                $filter->like('user.nickname','用户昵称');
                $filter->between('created_at','申请时间')->datetime();
            });
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
        $show = new Show(AgentMember::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('agent_id', __('Agent id'));
        $show->field('user_id', __('User id'));
        $show->field('amount', __('Amount'));
        $show->field('order_number', __('Order number'));
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
        $form = new Form(new AgentMember);

        $form->number('agent_id', __('Agent id'));
        $form->text('user_id', __('User id'));
        $form->decimal('amount', __('Amount'));
        $form->number('order_number', __('Order number'));

        return $form;
    }
}
