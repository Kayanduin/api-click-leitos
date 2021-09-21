<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('bed_type_id', false, true)->nullable(false);
            $table->bigInteger('total_beds', false, true)->nullable(false);
            $table->bigInteger('free_beds', false, true)->nullable(false);
            $table->bigInteger('health_unity_id', false, true)->nullable(false);
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
        Schema::dropIfExists('beds');
    }
}
