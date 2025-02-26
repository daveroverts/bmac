<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeAirportsNullableInFlightsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Schema::table('flights', function (Blueprint $table) {
        //     $table->unsignedInteger('dep')->nullable()->change();
        //     $table->unsignedInteger('arr')->nullable()->change();
        //     $table->dropForeign(['dep']);
        //     $table->dropForeign(['arr']);
        //     $table->foreign('dep')->references('id')->on('airports')->nullOnDelete();
        //     $table->foreign('arr')->references('id')->on('airports')->nullOnDelete();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('flights', function (Blueprint $table) {
        //     $table->unsignedInteger('dep')->change();
        //     $table->unsignedInteger('arr')->change();
        //     $table->dropForeign(['dep']);
        //     $table->dropForeign(['arr']);
        //     $table->foreign('dep')->references('id')->on('airports');
        //     $table->foreign('arr')->references('id')->on('airports');
        // });
    }
}
