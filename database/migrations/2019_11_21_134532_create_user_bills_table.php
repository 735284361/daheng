<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_bills', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->comment('用户编号');
            $table->string('bill_name')->nullable()->comment('账单名称');
            $table->double('amount',10,2)->comment('交易金额');
            $table->integer('amount_type')->comment('交易类型 -1 出账，1 入账');
            $table->integer('status')->comment('账单状态');
            $table->integer('billable_id')->comment('对象的ID');
            $table->string('billable_type')->comment('归属父模型的类名');
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
        Schema::dropIfExists('user_bills');
    }
}
