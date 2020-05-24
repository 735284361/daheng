<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refund_bills', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no')->comment('订单号');
            $table->string('refund_no')->comment('退款单号');
            $table->tinyInteger('refund_type')->comment('退款类型 1-退运费；2-商品退款');
            $table->double('refund_amount',10,2)->comment('退款金额');
            $table->string('refund_desc')->comment('退货原因');
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
        Schema::dropIfExists('refund_bills');
    }
}
