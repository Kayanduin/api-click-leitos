<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigInteger('user_id', false, true);

            $table->integer('create_bed', false, true)->nullable(false);
            $table->integer('read_bed', false, true)->nullable(false);
            $table->integer('update_bed', false, true)->nullable(false);
            $table->integer('delete_bed', false, true)->nullable(false);

            $table->integer('create_user', false, true)->nullable(false);
            $table->integer('read_user', false, true)->nullable(false);
            $table->integer('update_user', false, true)->nullable(false);
            $table->integer('delete_user', false, true)->nullable(false);

            $table->integer('create_health_unity', false, true)->nullable(false);
            $table->integer('read_health_unity', false, true)->nullable(false);
            $table->integer('update_health_unity', false, true)->nullable(false);
            $table->integer('delete_health_unity', false, true)->nullable(false);

            $table->integer('create_samu_unity', false, true)->nullable(false);
            $table->integer('read_samu_unity', false, true)->nullable(false);
            $table->integer('update_samu_unity', false, true)->nullable(false);
            $table->integer('delete_samu_unity', false, true)->nullable(false);

            $table->timestamps();
            $table->bigInteger('created_by', false, true)->nullable(false);

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
        Schema::dropIfExists('permissions');
    }
}
