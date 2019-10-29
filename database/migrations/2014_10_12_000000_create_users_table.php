<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nickname');
            $table->string('union_id')->nullable()->unique();
            $table->string('open_id')->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->text('avatar')->comment('头像');
            $table->string('session_key')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
