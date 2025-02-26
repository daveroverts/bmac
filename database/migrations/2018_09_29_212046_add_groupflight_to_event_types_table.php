<?php

use Illuminate\Database\Migrations\Migration;

class AddGroupflightToEventTypesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('event_types')->insert([
            'id' => 4, 'name' => 'Groupflight'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('event_types')->delete(4);
    }
}
