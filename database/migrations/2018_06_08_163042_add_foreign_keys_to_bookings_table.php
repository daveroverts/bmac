<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('reservedBy_id')->references('id')->on('users');
            $table->foreign('bookedBy_id')->references('id')->on('users');
            $table->foreign('dep')->references('icao')->on('airports');
            $table->foreign('arr')->references('icao')->on('airports');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropForeign(['reservedBy_id']);
            $table->dropForeign(['bookedBy_id']);
            $table->dropForeign(['dep']);
            $table->dropForeign(['arr']);
        });
    }
}
