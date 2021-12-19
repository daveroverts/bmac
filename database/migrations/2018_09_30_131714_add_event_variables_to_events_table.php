<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEventVariablesToEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_oceanic_event')->default(false)->after('endBooking');
            $table->boolean('multiple_bookings_allowed')->default(true)->after('endBooking');
            $table->boolean('uses_times')->default(false)->after('endBooking');
            $table->boolean('import_only')->default(false)->after('endBooking');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['is_oceanic_event', 'multiple_bookings_allowed', 'uses_times', 'import_only']);
        });
    }
}
