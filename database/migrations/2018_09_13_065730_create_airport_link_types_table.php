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
        Schema::create('airport_link_types', function (Blueprint $table): void {
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
     */
    public function down(): void
    {
        Schema::dropIfExists('airport_link_types');
    }
};
