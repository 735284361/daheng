<?php

namespace App\Admin\Controllers;

use App\Models\SysParams;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SysParamsController extends AdminController
{

    protected static $status;

    public function __construct()
    {
        self::$status = SysParams::getStatus();
    }

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\SysParams';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SysParams);

        $grid->column('id', __('ID'));
        $grid->column('code', __('编号'));
        $grid->column('value', __('内容'));
        $grid->column('remark', __('备注'));
        $grid->column('status', __('状态'))->display(function($status) {
            return SysParams::getStatus($status);
        });
//        $grid->column('switch_status', __('Switch status'));
//        $grid->column('type', __('Type'));
        $grid->column('created_at', __('创建时间'));
        $grid->column('updated_at', __('更新时间'));

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
        $show = new Show(SysParams::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('code', __('编号'));
        $show->field('value', __('内容'));
        $show->field('remark', __('备注'));
        $show->field('status', __('状态'))->as(function($status) {
            return SysParams::getStatus($status);
        });
//        $show->field('switch_status', __('Switch status'));
//        $show->field('type', __('Type'));
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
        $form = new Form(new SysParams);

        $form->text('code', __('编号'))->rules('required');
        $form->textarea('value', __('内容'))->rules('required',['required'=>'必填']);
        $form->text('remark', __('备注'));
        $form->select('status', __('状态'),'123')->options(self::$status)->rules('required',['required'=>'必填']);
//        $form->switch('switch_status', __('Switch status'));
//        $form->switch('type', __('Type'))->default(1);

        return $form;
    }
}
