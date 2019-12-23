<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWithdrawTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdraw', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->comment('提现申请人');
            $table->string('withdraw_order')->comment('提现订单号');
            $table->double('apply_total',10,2)->comment('申请提现的金额');
            $table->tinyInteger('status')->default(1)->comment('提现状态');
            $table->timestamps();
        });

        Schema::create('withdraw_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('withdraw_id')->comment('提现记录ID');
            $table->string('remark')->nullable()->comment('备注');
            $table->tinyInteger('status')->comment('提现的状态');
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
        Schema::dropIfExists('withdraw');
        Schema::dropIfExists('withdraw_logs');
    }
}
