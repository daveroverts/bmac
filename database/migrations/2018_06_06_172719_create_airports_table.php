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
            // Can't use id() because that uses bigIncrements since Laravel 6, which might break existing envs at one point.
            $table->unsignedInteger('id')->autoIncrement();
            $table->string('icao')->unique();
            $table->string('iata')->unique();
            $table->string('name');
            $table->timestamps();
        });
        // DB::table('airports')->insert(['icao' => 'EHAM', 'iata' => 'AMS', 'name' => 'Amsterdam Airport Schiphol']);
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
