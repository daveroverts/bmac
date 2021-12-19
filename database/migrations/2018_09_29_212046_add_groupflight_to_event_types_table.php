<?php

use Illuminate\Database\Migrations\Migration;

class AddGroupflightToEventTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('event_types')->insert([
            'id' => 4, 'name' => 'Groupflight',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('event_types')->delete(4);
    }
}
