<?php

namespace App\Admin\Controllers;

use App\Models\Feedback;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FeedbackController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户反馈';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Feedback);

        $grid->model()->orderBy('id','desc');

        $grid->column('id', __('编号'));
        $grid->column('user_id', __('用户编号'));
        $grid->column('user.nickname', __('用户昵称'));
        $grid->column('type', __('反馈类型'))->using(Feedback::getTypes());
        $grid->column('content', __('反馈内容'))->limit(60);
        $grid->column('phone', __('联系电话'));
        $grid->column('created_at', __('创建时间'));

        $grid->disableCreateButton();
        $grid->expandFilter();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1/2,function ($filter) {
                $filter->like('user_id','用户编号');
                $filter->like('user.nickname','昵称');
            });

            $filter->column(1/2,function ($filter) {
                $filter->equal('type','状态')->select(Feedback::getTypes());
                $filter->between('created_at','下单时间')->datetime();
            });
        });

        $grid->actions(function($actions) {
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
        $show = new Show(Feedback::findOrFail($id));

        $show->field('id', __('编号'));
        $show->field('user_id', __('用户编号'));
        $show->field('type', __('类型'))->using(Feedback::getTypes());
        $show->field('content', __('内容'));
        $show->field('phone', __('电话'));
        $show->field('created_at', __('创建时间'));
        $show->field('updated_at', __('更新时间'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Feedback);

        $form->number('user_id', __('User id'));
        $form->text('type', __('Type'));
        $form->text('content', __('Content'));
        $form->mobile('phone', __('Phone'));

        return $form;
    }
}
