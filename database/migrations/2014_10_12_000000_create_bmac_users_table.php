<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBmacUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bmac_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('vatsim_id', 7)->unique();
            $table->string('email')->unique();
            $table->string('country');
            $table->string('region');
            $table->string('division');
            $table->string('subdivision')->nullable();
            $table->boolean('isAdmin')->default(0);

            $table->rememberToken();
            $table->timestamps();
        });
        DB::table('bmac_users')->insert(['name' => 'Administrator', 'vatsim_id' => 9999999, 'email' => 'admin@book-me-a-cookie.io', 'country' => 'NL', 'region' => 'Europe' , 'division' => 'Europe (except UK)', 'subdivision' => 'Dutch', 'isAdmin' => 1, 'created_at' => NOW(), 'updated_at' => NOW()]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bmac_users');
    }
}
