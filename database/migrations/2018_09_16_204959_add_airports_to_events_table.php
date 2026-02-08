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
        Schema::table('events', function (Blueprint $table): void {
            $table->unsignedInteger('arr')->after('description')->nullable();
            $table->unsignedInteger('dep')->after('description')->nullable();
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->foreign('dep')->references('id')->on('airports');
            $table->foreign('arr')->references('id')->on('airports');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropForeign(['dep']);
            $table->dropForeign(['arr']);
            $table->dropColumn(['dep', 'arr']);
        });
    }
};
