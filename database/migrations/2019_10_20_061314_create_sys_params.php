<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSysParams extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_params', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->comment('编号');
            $table->text('content')->nullable()->comment('内容');
            $table->string('remark')->nullable()->comment('备注');
            $table->tinyInteger('status')->default('10')->comment('参数状态'); // 10-公开参数；20-保密参数
            $table->tinyInteger('switch_status')->nullable()->comment('开关状态'); // 0-开；1-关
            $table->tinyInteger('type')->default('10')->comment('参数类型'); // 10-文本参数；20-开关参数
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
        Schema::dropIfExists('sys_params');
    }
}
