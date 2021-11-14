<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->string('email')->nullable(false);
            $table->string('password')->nullable(false);
            $table->string('cpf')->unique()->nullable(false);
            $table->boolean('first_time_login')->nullable(false);
            $table->bigInteger('role_id', false, true)->nullable(false);
            $table->boolean('deactivated_user')->nullable(false);
            $table->bigInteger('created_by', false, true)->nullable(false);
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
