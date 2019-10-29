<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsAttrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_attr', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('store_id')->default(0)->comment('店铺ID');
            $table->integer('goods_id')->comment('商品ID');
            $table->string('name')->comment('属性名称');
            $table->smallInteger('sort')->default(0)->comment('排序');
            $table->timestamps();

            $table->unique(['store_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_attr');
    }
}
