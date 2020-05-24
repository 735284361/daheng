<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRefundAmountToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order', function (Blueprint $table) {
            //
            $table->tinyInteger('refund_mark')->after('order_settlement_status')->default(0)->comment('退款标识 0-无退款 1-存在退款 2-全部退款');
            $table->double('refund_logistics_fee',10,2)->after('refund_mark')->default(0)->comment('运费退款金额');
            $table->double('refund_amount',10,2)->after('refund_logistics_fee')->default(0)->comment('退款金额');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order', function (Blueprint $table) {
            //
        });
    }
}
