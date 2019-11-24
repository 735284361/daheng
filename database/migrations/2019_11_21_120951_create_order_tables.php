<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no')->comment('订单编号');
            $table->string('escrow_trade_no')->comment('第三方支付流水号');
            $table->integer('user_id')->comment('用户ID');
            $table->integer('product_count')->comment('商品数量');
            $table->double('product_amount_total',10,2)->comment('商品总价');
            $table->integer('logistics_fee')->default(0)->comment('运费金额');
            $table->double('order_amount_total',10,2)->comment('实际付款金额');
            $table->timestamp('pay_time')->nullable()->comment('付款时间');
            $table->timestamp('delivery_time')->nullable()->comment('发货时间');
            $table->timestamp('order_settlement_time')->nullable()->comment('订单结算时间');
            $table->integer('order_settlement_status')->default(0)->comment('订单结算状态 0未结算 1已结算');
            $table->integer('after_status')->default(0)->comment('用户售后状态 0 未发起售后 1 申请售后 -1 售后已取消 2 处理中 200 处理完毕');
            $table->integer('status')->default(0)->comment('订单状态 0未付款,1已付款,2已发货,3已签收,-1退货申请,-2退货中,-3已退货,-4取消交易');
            $table->string('remark')->nullable()->comment('订单备注');
            $table->timestamps();
        });

        Schema::create('order_goods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no')->comment('订单编号');
            $table->string('goods_id')->comment('商品编号');
            $table->string('sku')->nullable()->comment('商品规格');
            $table->integer('product_count')->nullable()->comment('商品数量');
            $table->integer('product_price')->nullable()->comment('商品单价');
            $table->timestamps();
        });

        Schema::create('order_address', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no')->comment('订单号');
            $table->string('name')->comment('联系人');
            $table->string('phone')->comment('手机号');
            $table->string('province')->comment('省');
            $table->string('city')->comment('市');
            $table->string('county')->comment('县');
            $table->string('detail_info')->comment('详细地址');
            $table->string('postal_code')->comment('邮编')->nullable();
            $table->timestamps();
        });

        Schema::create('order_event_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no')->comment('订单编号');
            $table->integer('event')->comment('订单事件编号');
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
        Schema::dropIfExists('order');
        Schema::dropIfExists('order_goods');
        Schema::dropIfExists('order_address');
        Schema::dropIfExists('order_event_logs');
    }
}
