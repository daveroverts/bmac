<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('reservedBy_id')->nullable();
            $table->unsignedInteger('bookedBy_id')->nullable();
            $table->string('callsign',7)->nullable();
            $table->string('acType',4)->nullable();
            $table->string('selCal',5);
            $table->unsignedInteger('dep')->nullable();
            $table->unsignedInteger('arr')->nullable();
            $table->time('ctot')->nullable();
            $table->string('oceanicFL')->nullable();
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
        Schema::dropIfExists('bookings');
    }
}
