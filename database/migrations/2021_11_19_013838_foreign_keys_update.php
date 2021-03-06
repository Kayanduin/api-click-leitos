<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ForeignKeysUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_contacts', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });

        Schema::table('samu_unit_contacts', function (Blueprint $table) {
            $table->foreign('samu_unit_id')
                ->references('id')
                ->on('samu_units')
                ->onDelete('cascade');
        });

        Schema::table('health_unit_contacts', function (Blueprint $table) {
            $table->foreign('health_unit_id')
                ->references('id')
                ->on('health_units')
                ->onDelete('cascade');
        });

        Schema::table('samu_units', function (Blueprint $table) {
            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->onDelete('cascade');
            $table->foreign('created_by')
                ->references('id')
                ->on('users');
        });

        Schema::table('health_units', function (Blueprint $table) {
            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->onDelete('cascade');
            $table->foreign('created_by')
                ->references('id')
                ->on('users');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->foreign('state_id')
                ->references('id')
                ->on('states');
        });

        Schema::table('beds', function (Blueprint $table) {
            $table->foreign('bed_type_id')
                ->references('id')
                ->on('bed_types');
            $table->foreign('health_unit_id')
                ->references('id')
                ->on('health_units')
                ->onDelete('cascade');
            $table->foreign('created_by')
                ->references('id')
                ->on('users');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->foreign('city_id')
                ->references('id')
                ->on('cities');
            $table->foreign('created_by')
                ->references('id')
                ->on('users');
        });

        Schema::table('user_units', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
            $table->foreign('samu_unit_id')
                ->references('id')
                ->on('samu_units')
                ->onDelete('cascade');
            $table->foreign('health_unit_id')
                ->references('id')
                ->on('health_units')
                ->onDelete('cascade');
            $table->foreign('created_by')
                ->references('id')
                ->on('users');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')
                ->references('id')
                ->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
    }
}
