<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShippingFeeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_fee', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('province')->comment('省份');
            $table->integer('shipping_fee')->comment('基础运费');
            $table->integer('full_amount')->comment('满减金额');
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
        Schema::dropIfExists('shipping_fee');
    }
}
