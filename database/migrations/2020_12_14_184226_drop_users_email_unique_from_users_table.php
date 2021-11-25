<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUsersEmailUniqueFromUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        return;
        // Schema::table('users', function (Blueprint $table) {
        //     $table->dropUnique('users_email_unique');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        return;
        // Schema::table('users', function (Blueprint $table) {
        //     $table->unique('email');
        // });
    }
}
