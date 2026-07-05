<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->renameColumn('is_editable', 'is_actype_editable');
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->boolean('is_callsign_editable')->after('is_actype_editable')->default(false);
        });

        // Preserve the previous behaviour where a single flag controlled both fields.
        DB::table('bookings')->update(['is_callsign_editable' => DB::raw('is_actype_editable')]);
    }
};
