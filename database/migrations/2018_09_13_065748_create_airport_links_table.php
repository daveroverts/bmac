<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAirportLinksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('airport_links', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('airport_id');
            $table->unsignedInteger('airportLinkType_id');
            $table->string('name')->nullable();
            $table->string('url');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('airport_links', function (Blueprint $table): void {
            $table->foreign('airport_id')->references('id')->on('airports')->onDelete('cascade');
            $table->foreign('airportLinkType_id')->references('id')->on('airport_link_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('airport_links', function (Blueprint $table): void {
            $table->dropForeign(['airport_id']);
            $table->dropForeign(['airportLinkType_id']);
        });

        Schema::dropIfExists('airport_links');
    }
}
