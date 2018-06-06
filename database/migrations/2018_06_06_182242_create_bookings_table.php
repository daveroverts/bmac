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
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('reservedBy_id')->nullable();
            $table->unsignedInteger('bookedBy_id')->nullable();
            $table->string('callsign', 7)->nullable();
            $table->string('acType', 4)->nullable();
            $table->string('selCal', 5);
            $table->string('dep')->nullable();
            $table->string('arr')->nullable();
            $table->time('ctot')->nullable();
            $table->string('oceanicFL')->nullable();
            $table->timestamps();

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
            Schema::dropIfExists('bookings');
        }
    }