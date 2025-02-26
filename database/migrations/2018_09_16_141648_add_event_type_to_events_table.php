<?php

use App\Enums\EventType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventTypeToEventsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->tinyInteger('event_type_id')->unsigned()->after('id')->default(EventType::ONEWAY);
        });

        Schema::table('events', function (Blueprint $table): void {
            $table->foreign('event_type_id')->references('id')->on('event_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropForeign(['event_type_id']);
            $table->dropColumn('event_type_id');
        });
    }
}
