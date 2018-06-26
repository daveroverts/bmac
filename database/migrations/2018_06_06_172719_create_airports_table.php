<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAirportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('airports', function (Blueprint $table) {
            $table->string('icao')->primary();
            $table->unique('icao');
            $table->string('iata');
            $table->unique('iata');
            $table->string('name');
        });
        DB::table('airports')->insert(['icao' => 'EHAM', 'iata' => 'AMS', 'name' => 'Amsterdam Schiphol Airport']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('airports');
    }
}
