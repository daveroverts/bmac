<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAirportsToEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('arr')->after('description')->nullable();
            $table->string('dep')->after('description')->nullable();
        });

        Schema::table('events', function (Blueprint $table) {
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
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['dep']);
            $table->dropForeign(['arr']);
            $table->dropColumn(['dep', 'arr']);
        });
    }
}
