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
    protected $title = 'App\User';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User);

        $grid->column('id', __('ID'));
        $grid->column('nickname', __('用户名'));
//        $grid->column('union_id', __('Union id'));
//        $grid->column('open_id', __('Open id'));
//        $grid->column('phone', __('Phone'));
//        $grid->column('email', __('Email'));
//        $grid->column('password', __('Password'));
        $grid->column('avatar', __('头像'))->image('',100,100);
//        $grid->column('session_key', __('Session key'));
//        $grid->column('remember_token', __('Remember token'));
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
