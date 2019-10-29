<?php

namespace App\Admin\Controllers;

use App\Models\Goods;
use App\Models\GoodsAttr;
use App\Models\GoodsAttrValue;
use App\Models\GoodsSku;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class GoodsController extends AdminController
{

    protected static $statusArr;

    public function __construct()
    {
        self::$statusArr = Goods::getStatus();
    }
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App\Models\Goods';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Goods);

        $grid->column('id', __('ID'));
        $grid->column('name', __('商品名称'));
//        $grid->column('description', __('Description'));
        $grid->column('price', __('价格'));
        $grid->column('line_price', __('划线价'));
        $grid->column('stock_num', __('库存'))->hide();
        $grid->column('properties')->hide();
        $grid->column('content', __('Content'))->hide();
        $grid->column('sku_type', __('Sku type'))->hide();
        $grid->column('status', __('状态'))->display(function($status) {
            $statusCss = Goods::getStatus($status);
            if ($status == Goods::STATUS_ONLINE) {
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
        $show = new Show(Goods::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('user_id', __('User id'));
        $show->field('store_id', __('Store id'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));
        $show->field('price', __('Price'));
        $show->field('line_price', __('Line price'));
        $show->field('stock_num', __('Stock num'));
        $show->field('content', __('Content'));
        $show->field('sku_type', __('Sku type'));
        $show->field('status', __('Status'));
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
        $form = new Form(new Goods);

        $form->ignore(['sku']);
//        $form->number('user_id', __('User id'));
//        $form->number('store_id', __('Store id'));
        $form->text('name', __('商品名称'));
//        $form->text('description', __('Description'));
        $form->decimal('price', __('商品价格'))->default(0.00);
        $form->decimal('line_price', __('划线价格'))->default(0.00);
        $form->number('stock_num', __('商品库存'));
        $form->UEditor('content', __('图文详情'));

        $params = request()->route()->parameters();
        $sku = '';
        if ($params && $params['good']) {
            $goods = new Goods();
            $sku = $goods->getSku($params['good']);
        }
        $form->sku('sku', __('商品规格'))->default($sku);
        $form->select('status', __('商品状态'))->options(self::$statusArr);

        $form->saving(function($form) {
            $sku = json_decode(request('sku'),true);
            $form->model()->sku_type = $sku['type']; // 单独处理sku_type
        });

        $form->saved(function($form) {
            $sku = json_decode(request('sku'),true);

            if ($sku['type'] == 'many') {

                $id = $form->model()->id;
                // 保存attrs
                foreach ($sku['attrs'] as $k => $v) {
                    $data['goods_id'] = $id;
                    $data['name'] = $k;
                    $res = $form->model()->properties()->updateOrCreate($data);
                    foreach ($v as $vv) {
                        $childData['goods_attr_id'] = $res->id;
                        $childData['name'] = $vv;
                        $res->childsCurGoods()->updateOrCreate($childData);
                    }
                    // 删除不存在的
                    GoodsAttrValue::where('goods_attr_id',$res->id)->whereNotIn('name',array_values($v))->delete();
                }
                // 删除不存在的
                $form->model()->properties()->whereNotIn('name',array_keys($sku['attrs']))->delete();

                // 处理sku表
                foreach ($sku['sku'] as $v) {
                    $skuData['goods_id'] = $id;
                    $skuData['sku'] = $v;
                    $res = $form->model()->skuArr()->updateOrCreate($skuData);
                    $resArr[] = $res->id;
                }
                $form->model()->skuArr()->whereNotIn('id',$resArr)->delete();
            }

        });
        return $form;
    }
}
