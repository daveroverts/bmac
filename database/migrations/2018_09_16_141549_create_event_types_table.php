<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_types', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('name');
        });

        $types = [
            ['id' =>  '1', 'name' => 'One way'],
            ['id' =>  '2', 'name' => 'City-pair'],
            ['id' =>  '3', 'name' => 'Fly-In'],
        ];
        DB::table('event_types')->insert($types);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_types');
    }
}
