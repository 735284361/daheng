<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->comment('用户编号');
            $table->integer('status')->default(0)->comment('代理状态0 申请，1 正常，-1 禁用，-2 审核未通过');
            $table->string('qrcode')->nullable()->comment('代理商二维码');
            $table->timestamps();
        });

        Schema::create('agent_event_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('event')->comment('事件编码');
            $table->string('remark')->comment('事件备注');
            $table->timestamps();
        });

        Schema::create('agent_members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('agent_id')->comment('代理商编号');
            $table->string('user_id')->comment('用户编号');
            $table->double('amount')->default(0)->comment('消费金额');
            $table->integer('order_number')->default(0)->comment('订单数量');
            $table->timestamps();
        });

        Schema::create('agent_order_maps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('agent_id')->comment('代理商编号');
            $table->string('order_no')->unique()->comment('订单编号');
            $table->double('commission',10,2)->comment('佣金');
            $table->integer('status')->default(0)->comment('佣金结算状态');
            $table->integer('status_divide')->default(0)->comment('分成结算状态');
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
        Schema::dropIfExists('agent_tables');
        Schema::dropIfExists('agent_event_logs');
        Schema::dropIfExists('agent_members');
        Schema::dropIfExists('agent_order_maps');
    }
}
