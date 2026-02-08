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
        Schema::create('bookings', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('callsign', 7)->nullable();
            $table->string('acType', 4)->nullable();
            $table->string('selcal', 5)->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
