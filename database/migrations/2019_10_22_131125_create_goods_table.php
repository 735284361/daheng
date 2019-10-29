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
            $table->integer('stock_num')->default(0)->comment('库存');
            $table->longText('content')->comment('产品详情');
            $table->string('sku_type')->comment('规格类型');
            $table->tinyInteger('status')->default(10)->comment('状态');
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
