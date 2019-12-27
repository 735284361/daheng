<?php

namespace App\Admin\Controllers;

use App\Models\GoodsCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class GoodsCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商品分类';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GoodsCategory);

        $grid->model()->orderBy('id','desc');

        $grid->column('id', __('ID'));
        $grid->column('name', __('分类名称'));
        $grid->column('sort', __('分类排序'));
        $grid->column('status', __('分类状态'))->display(function($status) {
            $statusCss = GoodsCategory::getStatus($status);
            if ($status == GoodsCategory::STATUS_ENABLE) {
                $statusCss = "<span class='label label-success'>$statusCss</span>";
            } else {
                $statusCss = "<span class='label label-warning'>$statusCss</span>";
            }
            return $statusCss;
        });
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
        $show = new Show(GoodsCategory::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('name', __('分类名称'));
        $show->field('sort', __('分类排序'));
        $show->field('status', __('分类状态'))->display(function($status) {
            $statusCss = GoodsCategory::getStatus($status);
            if ($status == GoodsCategory::STATUS_ENABLE) {
                $statusCss = "<span class='label label-success'>$statusCss</span>";
            } else {
                $statusCss = "<span class='label label-warning'>$statusCss</span>";
            }
            return $statusCss;
        });
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
        $form = new Form(new GoodsCategory);

        $form->text('name', __('分类名称'))->rules(['required']);
        $form->number('sort', __('分类排序'))->default(255)->rules(['required']);
        $form->select('status', __('分类状态'))->default(20)->options(GoodsCategory::getStatus())->rules(['required']);

        return $form;
    }
}
