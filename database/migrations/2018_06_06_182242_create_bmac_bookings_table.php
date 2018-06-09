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
        Schema::create('bmac_bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('reservedBy_id')->nullable();
            $table->unsignedInteger('bookedBy_id')->nullable();
            $table->string('callsign', 7)->nullable();
            $table->string('acType', 4)->nullable();
            $table->string('selCal', 5)->nullable();
            $table->string('dep')->nullable();
            $table->string('arr')->nullable();
            $table->time('ctot')->nullable();
            $table->string('route')->nullable();
            $table->string('oceanicFL',3)->nullable();
            $table->string('oceanicTrack',1)->nullable();
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
            Schema::table('bmac_bookings', function (Blueprint $table) {

            });
            Schema::dropIfExists('bmac_bookings');
        }
    }