<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsAttrValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_attr_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('goods_attr_id')->comment('规格ID');
            $table->integer('store_id')->default(0)->comment('店铺ID');
            $table->string('name')->comment('属性名称');
            $table->smallInteger('sort')->default(0)->comment('排序');
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
        Schema::dropIfExists('goods_attr_values');
    }
}
