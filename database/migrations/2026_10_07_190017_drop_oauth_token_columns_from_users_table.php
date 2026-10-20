<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['access_token', 'refresh_token', 'token_expires']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('access_token')->after('remember_token')->nullable();
            $table->text('refresh_token')->after('access_token')->nullable();
            $table->unsignedBigInteger('token_expires')->after('refresh_token')->nullable();
        });
    }
};
