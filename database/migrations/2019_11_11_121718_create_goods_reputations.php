<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsReputations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_reputations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('goods_id')->comment('商品ID');
            $table->string('order_no')->comment('订单号');
            $table->integer('user_id')->comment('用户ID');
            $table->string('content')->comment('评论内容');
            $table->tinyInteger('score')->comment('评分');
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
        Schema::dropIfExists('goods_reputations');
    }
}
