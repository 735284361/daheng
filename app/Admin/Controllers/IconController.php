<?php

namespace App\Admin\Controllers;

use App\Models\GoodsCategory;
use App\Models\Icon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class IconController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '图标';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Icon);

        $grid->column('icon_img', __('图标'))->image('',150,150);;
        $grid->column('title', __('标题'));
        $grid->column('status', __('状态'))->using(Icon::getStatus());
        $grid->column('sort', __('排序'));

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
        $show = new Show(Icon::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('icon_img', __('Icon img'));
        $show->field('title', __('Title'));
        $show->field('status', __('Status'));
        $show->field('sort', __('Sort'));
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
        $form = new Form(new Icon);

        $category = GoodsCategory::all();
        $list = $category->pluck('name','id')->all();
        $form->image('icon_img', __('图标'))->uniqueName()->rules('required|max:100');
        $form->select('category_id', __('关联分类'))->options($list)->required();
        $form->text('title', __('标题'))->required();
        $form->select('status', __('状态'))->default(Icon::STATUS_OFF)
            ->options(Icon::getStatus())->required();
        $form->number('sort', __('排序'))->default(255)->min(0);

        return $form;
    }
}
