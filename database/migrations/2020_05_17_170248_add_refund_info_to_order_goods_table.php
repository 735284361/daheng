<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRefundInfoToOrderGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_goods', function (Blueprint $table) {
            //
            $table->double('refund_total_amount',10,2)->after('comment')->default(0)->comment('退款总额 该商品单价*退款数量的总额');
            $table->integer('refund_product_count')->after('comment')->default(0)->comment('退款数量');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_goods', function (Blueprint $table) {
            //
        });
    }
}
