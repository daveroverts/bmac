<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBmacAirportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bmac_airports', function (Blueprint $table) {
            $table->string('icao')->primary();
            $table->unique('icao');
            $table->string('iata');
            $table->unique('iata');
            $table->string('name');
        });
        DB::table('bmac_airports')->insert(['icao' => 'EHAM', 'iata' => 'AMS', 'name' => 'Amsterdam Schiphol Airport']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bmac_airports');
    }
}
