<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToBmacBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bmac_bookings', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('bmac_events')->onDelete('cascade');
            $table->foreign('reservedBy_id')->references('id')->on('bmac_users');
            $table->foreign('bookedBy_id')->references('id')->on('bmac_users');
            $table->foreign('dep')->references('icao')->on('bmac_airports');
            $table->foreign('arr')->references('icao')->on('bmac_airports');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bmac_bookings', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropForeign(['reservedBy_id']);
            $table->dropForeign(['bookedBy_id']);
            $table->dropForeign(['dep']);
            $table->dropForeign(['arr']);
        });
    }
}
