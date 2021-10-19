<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SamuUnitContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('samu_unit_contacts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('samu_unit_id', false, true)->nullable(false);
            $table->string('telephone_number')->nullable(false);
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
        Schema::dropIfExists('samu_unit_contacts');
    }
}
