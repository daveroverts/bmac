<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->unsignedInteger('id')->primary();
            $table->string('name_first', 50);
            $table->string('name_last', 50);
            $table->string('email');
            $table->string('country');
            $table->string('region');
            $table->string('division');
            $table->string('subdivision')->nullable();
            $table->boolean('isAdmin')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
