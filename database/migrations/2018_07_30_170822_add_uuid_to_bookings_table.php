<?php

use App\Models\Booking;
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
        Schema::table('bookings', function (Blueprint $table): void {
            $table->uuid('uuid')->after('id')->nullable()->unique();
            $table->index('uuid');
        });
        foreach (Booking::all() as $booking) {
            $booking->uuid = (string)Uuid::generate(4);
            $booking->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn('uuid');
        });
    }
};
