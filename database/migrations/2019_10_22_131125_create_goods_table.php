<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods', function (Blueprint $table) {
            $table->increments('id')->comment('商品ID');
            $table->integer('user_id')->comment('用户ID');
            $table->integer('store_id')->comment('商家ID');
            $table->string('name')->comment('商品名称');
            $table->string('description')->comment('描述');
            $table->decimal('price')->default(0)->comment('价格');
            $table->decimal('line_price')->default(0)->nullable()->comment('划线价');
            $table->integer('stock')->default(0)->comment('库存');
            $table->integer('category_id')->comment('商品分类');
            $table->tinyInteger('recommend_status')->default(20)->comment('是否推荐');
            $table->integer('sort')->default(255)->comment('排序');
            $table->integer('number_fav')->default(0)->comment('收藏数');
            $table->integer('number_reputation')->default(0)->comment('评论数');
            $table->integer('number_score')->default(0)->comment('总评分');
            $table->integer('number_orders')->default(0)->comment('订单数');
            $table->integer('number_sells')->default(0)->comment('销售数');
            $table->integer('number_views')->default(0)->comment('浏览数');
            $table->tinyInteger('status')->default(20)->comment('状态');
            $table->string('pic_url')->comment('图片地址');
            $table->json('pics')->comment('轮播图片');
            $table->string('sku_type')->comment('规格类型');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods');
    }
}
