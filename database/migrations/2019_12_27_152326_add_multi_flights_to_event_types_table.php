<?php

use Illuminate\Database\Migrations\Migration;

class AddMultiFlightsToEventTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('event_types')->insert([
            'id' => 5, 'name' => 'Multi flights',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('event_types')->delete(5);
    }
}
