<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentTeamTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 代理团队
        Schema::create('agent_team', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->comment('团队队长UID');
            $table->string('qrcode')->nullable()->comment('团队邀请二维码');
            $table->integer('status')->default(0)->comment('团队状态0 申请，1 正常，-1 禁用，-2 审核未通过 -3 失效');
            $table->timestamps();
        });

        // 代理团队账单 每个月结算的时候记录一次
        Schema::create('agent_team_bills', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('team_id')->comment('团队ID');
            $table->integer('user_id')->comment('团队编号');
            $table->string('month')->comment('账单月份');
            $table->double('sales_volume',10,2)->default(0)->comment('团队销售总额');
            $table->integer('divide_status')->default(0)->comment('分成状态 0-未分成；1-已分成');
            $table->double('divide_total_amount',10,2)->default(0)->comment('奖金总额');
            $table->double('divide_remain_amount',10,2)->default(0)->comment('奖金剩余额 此数值对应每月队长的特殊提成');
            $table->timestamps();
        });

        // 代理团队和代理成员关联表
        Schema::create('agent_team_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('team_id')->comment('团队ID');
            $table->integer('user_id')->comment('队员ID');
            $table->timestamps();
        });

        // 代理成员分销账单 每一单更新一次
        Schema::create('agent_bills', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->comment('用户ID');
            $table->string('month')->comment('账单月份');
            $table->double('sales_volume',10,2)->default(0)->comment('销售额');
            $table->integer('divide_status')->default(0)->comment('分成状态 0-未分成；1-已分成');
            $table->double('divide_amount',10,2)->default(0)->comment('分成金额');
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
        Schema::dropIfExists('agent_team');
        Schema::dropIfExists('agent_team_bills');
        Schema::dropIfExists('agent_team_users');
        Schema::dropIfExists('agent_bills');
    }
}
