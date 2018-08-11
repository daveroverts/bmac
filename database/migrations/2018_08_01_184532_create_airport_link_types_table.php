<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAirportLinkTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('airport_link_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('class')->nullable();
            $table->timestamps();
        });
        $types = [
            ['name' =>  'Briefing',],
            ['name' =>  'Charts',],
            ['name' =>  'Scenery'],
            ['name' =>  'Miscellaneous',],
        ];
        DB::table('airport_link_types')->insert($types);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('airport_link_types');
    }
}
