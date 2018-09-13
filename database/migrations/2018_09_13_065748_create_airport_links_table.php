<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAirportLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('airport_links', function (Blueprint $table) {
            $table->increments('id');
            $table->string('icao_airport');
            $table->unsignedInteger('airportLinkType_id');
            $table->string('name')->nullable();
            $table->string('url');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('airport_links', function (Blueprint $table) {
            $table->foreign('icao_airport')->references('icao')->on('airports')->onDelete('cascade');
            $table->foreign('airportLinkType_id')->references('id')->on('airport_link_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('airport_links', function (Blueprint $table) {
            $table->dropForeign(['icao_airport']);
            $table->dropForeign(['airportLinkType_id']);
        });

        Schema::dropIfExists('airport_links');
    }
}
