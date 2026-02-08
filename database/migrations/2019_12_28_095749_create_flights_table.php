<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flights', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedInteger('booking_id');
            $table->smallInteger('order_by')->default(1);
            $table->unsignedInteger('dep')->nullable();
            $table->unsignedInteger('arr')->nullable();
            $table->dateTime('ctot')->nullable();
            $table->dateTime('eta')->nullable();
            $table->string('route')->nullable();
            $table->string('oceanicFL', 3)->nullable();
            $table->string('oceanicTrack', 2)->nullable();
            $table->timestamps();

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('dep')->references('id')->on('airports');
            $table->foreign('arr')->references('id')->on('airports');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
