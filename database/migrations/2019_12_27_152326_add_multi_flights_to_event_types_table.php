<?php

use Illuminate\Database\Migrations\Migration;

class AddMultiFlightsToEventTypesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('event_types')->insert([
            'id' => 5, 'name' => 'Multi flights'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('event_types')->delete(5);
    }
}
