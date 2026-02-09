<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_links', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('event_link_type_id');
            $table->string('name')->nullable();
            $table->string('url');
            $table->timestamps();
        });

        // We can't use $table->foreignId()->constrained() due to different different datatype
        Schema::table('event_links', function (Blueprint $table): void {
            $table->foreign('event_id')->references('id')->on('events')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('event_link_type_id')->references('id')->on('airport_link_types')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_links');
    }
};
