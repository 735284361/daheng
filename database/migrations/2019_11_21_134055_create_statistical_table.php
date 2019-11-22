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
            $table->bigIncrements('id');
            $table->timestamp('day')->comment('日期');
            $table->double('sales')->default(0)->comment('销售额');
            $table->double('share_amount')->default(0)->comment('分成金额');
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
