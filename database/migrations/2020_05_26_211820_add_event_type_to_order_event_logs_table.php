<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventTypeToOrderEventLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_event_logs', function (Blueprint $table) {
            //
            $table->tinyInteger('event_type')->after('event')->default(1)->comment('事件类型 1-正常事件，2-退款事件');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_event_logs', function (Blueprint $table) {
            //
        });
    }
}
