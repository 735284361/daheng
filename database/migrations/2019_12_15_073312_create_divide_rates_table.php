<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDivideRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('divide_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('sales_start')->comment('销售额');
            $table->integer('sales_end')->comment('销售额');
            $table->integer('proportion')->comment('分成比例');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('divide_rates');
    }
}
