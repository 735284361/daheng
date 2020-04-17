<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatisticalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statistical', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('date')->comment('日期');
            $table->double('sales')->default(0)->comment('销售额');
            $table->double('share_amount')->default(0)->comment('分成金额');
            $table->double('order_count')->default(0)->comment('新增订单数');
            $table->double('new_user_count')->default(0)->comment('新增用户数');
            $table->double('new_user_count')->default(0)->comment('新增用户数');
            $table->double('new_user_count')->default(0)->comment('新增用户数');
            $table->double('new_user_count')->default(0)->comment('新增用户数');
            $table->double('feedback_count')->default(0)->comment('用户反馈');
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
        Schema::dropIfExists('statistical');
    }
}
