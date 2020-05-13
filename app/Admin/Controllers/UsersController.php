<?php

namespace App\Admin\Controllers;

use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UsersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User);

        $grid->model()->orderBy('id','desc');

        $grid->column('id', __('用户编号'));
        $grid->column('nickname', __('用户名'));
        $grid->column('phone', __('电话'))->sortable();
        $grid->column('avatar', __('头像'))->image('',50,50);
        $grid->column('account.balance', __('余额'))->sortable();
        $grid->column('account.withdrawn', __('已提现'))->sortable();
        $grid->column('account.cash_in', __('提现中'))->sortable();
        $grid->column('created_at', __('创建时间'));
        $grid->column('updated_at', __('修改时间'));

        $grid->disableCreateButton();
        $grid->expandFilter();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2,function ($filter) {
                $filter->like('id','用户编号');
                $filter->like('nickname','用户昵称');
            });

            $filter->column(1/2,function ($filter) {
                $filter->like('phone','用户电话');
                $filter->between('created_at','创建时间')->datetime();
            });
        });

        $grid->actions(function($actions) {
            $actions->disableEdit();
            $actions->disableDelete();
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
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('nickname', __('Nickname'));
        $show->field('union_id', __('Union id'));
        $show->field('open_id', __('Open id'));
        $show->field('phone', __('Phone'));
        $show->field('email', __('Email'));
        $show->field('password', __('Password'));
        $show->field('avatar', __('Avatar'));
        $show->field('session_key', __('Session key'));
        $show->field('remember_token', __('Remember token'));
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
        $form = new Form(new User);

        $form->text('nickname', __('Nickname'));
        $form->text('union_id', __('Union id'));
        $form->text('open_id', __('Open id'));
        $form->mobile('phone', __('Phone'));
        $form->email('email', __('Email'));
        $form->password('password', __('Password'));
        $form->textarea('avatar', __('Avatar'));
        $form->text('session_key', __('Session key'));
        $form->text('remember_token', __('Remember token'));

        return $form;
    }
}
