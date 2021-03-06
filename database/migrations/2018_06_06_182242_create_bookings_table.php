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
            $table->string('selcal', 5)->nullable();
            $table->string('dep')->nullable();
            $table->string('arr')->nullable();
            $table->dateTime('ctot')->nullable();
            $table->string('route')->nullable();
            $table->string('oceanicFL',3)->nullable();
            $table->string('oceanicTrack',2)->nullable();
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