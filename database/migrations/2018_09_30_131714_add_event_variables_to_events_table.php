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
        Schema::table('events', function (Blueprint $table): void {
            $table->boolean('is_oceanic_event')->default(false)->after('endBooking');
            $table->boolean('multiple_bookings_allowed')->default(true)->after('endBooking');
            $table->boolean('uses_times')->default(false)->after('endBooking');
            $table->boolean('import_only')->default(false)->after('endBooking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn(['is_oceanic_event', 'multiple_bookings_allowed', 'uses_times', 'import_only']);
        });
    }
};
