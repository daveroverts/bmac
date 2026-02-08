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
        Schema::create('events', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->text('description');
            $table->dateTime('startEvent');
            $table->dateTime('endEvent');
            $table->dateTime('startBooking');
            $table->dateTime('endBooking');
            $table->dateTime('sendFeedbackForm')->nullable();
            $table->boolean('formSent')->default(1);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
