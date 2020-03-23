<?php

namespace App\Admin\Controllers;

use App\Models\Goods;
use App\Models\GoodsAttrValue;
use App\Models\GoodsCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

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
    protected $title = '商品';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Goods);

        $grid->model()->orderBy('id','desc');

        $grid->column('id', __('ID'));
        $grid->column('user_id', __('User id'))->hide();
        $grid->column('store_id', __('Store id'))->hide();
        $grid->column('name', __('商品名'))->sortable();
        $grid->column('pic_url', __('图片'))->image('',100,100);
        $grid->column('description', __('Description'))->hide();
        $grid->column('price', __('价格'))->sortable();
        $grid->column('line_price', __('Line price'))->hide();
        $grid->column('stock', __('Stock num'))->hide();
        $grid->column('category_id', __('Category id'))->hide();
        $grid->column('sort', __('Sort'))->hide();
//        $grid->column('number_fav', __('收藏数'))->sortable();
//        $grid->column('number_reputation', __('评论量'))->sortable();
        $grid->column('number_orders', __('订单量'))->sortable();
        $grid->column('number_sells', __('销量'))->sortable();
        $grid->column('number_views', __('浏览量'))->sortable();
        $grid->column('status', __('状态'))->display(function($status) {
            $statusCss = Goods::getStatus($status);
            if ($status == Goods::STATUS_ONLINE) {
                $statusCss = "<span class='label label-success'>$statusCss</span>";
            } else {
                $statusCss = "<span class='label label-warning'>$statusCss</span>";
            }
            return $statusCss;
        });
        $grid->column('recommend_status', __('推荐'))->display(function($status) {
            $statusCss = Goods::getRecommendStatus($status);
            if ($status == Goods::STATUS_ONLINE) {
                $statusCss = "<span class='label label-danger'>$statusCss</span>";
            } else {
//                $statusCss = "<span class='label label-primary'>$statusCss</span>";
            }
            return $statusCss;
        });
        $grid->column('sku_type', __('Sku type'))->hide();
        $grid->column('created_at', __('创建时间'))->sortable();
        $grid->column('updated_at', __('更新时间'))->sortable();

        $grid->filter(function($filter) {
            $filter->like('name','商品名称');
            $filter->equal('status','状态')->select(Goods::getStatus());
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
        $show = new Show(Goods::findOrFail($id));

        $show->field('id', __('ID'));
//        $show->field('user_id', __('User id'));
//        $show->field('store_id', __('Store id'));
        $show->field('name', __('商品名称'));
//        $show->field('description', __('Description'));
        $show->field('price', __('商品价格'));
        $show->field('line_price', __('划线价格'));
        $show->field('stock', __('商品库存'));
        $show->field('category_id', __('商品分类'));
        $show->field('sort', __('Sort'));
//        $show->field('number_fav', __('Number fav'));
        $show->field('number_reputation', __('Number reputation'));
        $show->field('number_orders', __('Number orders'));
        $show->field('number_sells', __('Number sells'));
        $show->field('number_views', __('Number views'));
        $show->field('status', __('Status'));
        $show->field('pic_url', __('Pic url'));
        $show->field('sku_type', __('Sku type'));
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
        $form->text('name', __('商品名称'))->rules(['required']);
//        $form->text('description', __('Description'));
        $form->decimal('price', __('商品价格'))->default(0.00)->rules(['required']);
        $form->decimal('line_price', __('划线价格'))->default(0.00)->rules(['required']);
        $form->decimal('dist_price', __('分成价格'))->default(0.00)->rules(['required'])->help('当该商品为单规格时，此分成价格有效');
        $form->number('stock', __('商品库存'))->default(0)->rules(['required']);
        $form->select('category_id', __('商品分类'))->options(function() {
            $categories = GoodsCategory::where('status',GoodsCategory::STATUS_ENABLE)->get(['id','name'])->toArray();
            $arr=[];
            if ($categories) {
                foreach ($categories as $category) {
                    $arr[$category['id']] = $category['name'];
                }
            }
            return $arr;
        })->required();
        $form->select('recommend_status', __('是否推荐'))->default(Goods::RECOMMEND_STATUS_NO)->options(Goods::getRecommendStatus());
        $form->number('sort', __('排序'))->default(255)->required();
//        $form->number('number_fav', __('收藏数量'))->default(0);
        $form->number('number_orders', __('订单数量'))->default(0);
        $form->number('number_views', __('浏览数量'))->default(0);
        $form->number('number_reputation', __('评论数量'))->default(0)->readonly();
        $form->number('number_sells', __('销售数量'))->default(0)->readonly();
        $form->select('status', __('商品状态'))->default(GoodsCategory::STATUS_DISABLE)->options(self::$statusArr)->required();
        $form->image('pic_url',__('商品头图'))->required();
        $form->multipleImage('pics',__('轮播图片'))->help('请上传多张图片，上传时按住ctrl键选择多张图片')->removable()->sortable();

        $form->UEditor('content.content',__('商品介绍'));

        $params = request()->route()->parameters();
        $sku = '';
        if ($params && $params['good']) {
            $goods = new Goods();
            $sku = $goods->getSku($params['good']);
        }
        $form->sku('sku', __('商品规格'))->default($sku);
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
