<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_units', function (Blueprint $table) {
            $table->bigInteger('user_id', false, true);
            $table->bigInteger('samu_unit_id', false, true)->nullable(true);
            $table->bigInteger('health_unit_id', false, true)->nullable(true);
            $table->bigInteger('created_by', false, true)->nullable(false);
            $table->timestamps();

            $table->primary('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_units');
    }
}
