<?php

namespace App\Admin\Controllers;

use App\Models\Banner;
use App\Models\Goods;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BannerController extends AdminController
{

    protected static $status;

    public function __construct()
    {
        self::$status = Banner::getStatus();
    }

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '轮播';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Banner);

        $grid->model()->orderBy('id','desc');

        $grid->column('id', __('ID'));
        $grid->column('goods_id', __('商品ID'));
        $grid->column('sort', __('排序'));
        $grid->column('pic_url', __('图片'))->image('',150,150);
        $grid->column('status', __('状态'))->display(function($status) {
            $statusCss = Banner::getStatus($status);
            if ($status == Banner::STATUS_ONLINE) {
                $statusCss = "<span class='label label-success'>$statusCss</span>";
            } else {
                $statusCss = "<span class='label label-warning'>$statusCss</span>";
            }
            return $statusCss;
        });
        $grid->column('title', __('标题'));
        $grid->column('created_at', __('创建时间'));
//        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Banner::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('goods_id', __('商品编号'));
        $show->field('sort', __('排序'));
        $show->field('pic_url', __('图片'))->image();
        $show->field('status', __('状态'))->as(function($status) {
            return Banner::getStatus($status);
        });
        $show->field('title', __('标题'));
        $show->field('created_at', __('创建时间'));
        $show->field('updated_at', __('修改时间'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Banner);

        $goods = Goods::all();
        $list = $goods->pluck('name','id')->all();
        $form->select('goods_id', __('商品'))->options($list)->rules('required');
        $form->number('sort', __('排序'))->default(255)->min(0);
        $form->select('status', __('状态'))->default(Banner::STATUS_ONLINE)
            ->options(Banner::getStatus())->rules('required');
        $form->text('title', __('标题'));
        $form->image('pic_url', __('图片'))->uniqueName()->rules('required|max:500');

        return $form;
    }
}
