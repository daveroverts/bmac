<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('airports', function (Blueprint $table): void {
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
     */
    public function down(): void
    {
        Schema::dropIfExists('airports');
    }
};
