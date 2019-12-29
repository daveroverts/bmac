<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFlightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('booking_id');
            $table->smallInteger('order_by')->default(1);
            $table->unsignedInteger('dep');
            $table->unsignedInteger('arr');
            $table->dateTime('ctot')->nullable();
            $table->dateTime('eta')->nullable();
            $table->string('route')->nullable();
            $table->string('oceanicFL', 3)->nullable();
            $table->string('oceanicTrack', 2)->nullable();
            $table->timestamps();

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('dep')->references('id')->on('airports');
            $table->foreign('arr')->references('id')->on('airports');
        });

        // All existing bookings will now be coupled to a flight
        DB::table('bookings')->chunkById(100, function ($bookings) {
            foreach ($bookings as $booking) {
                DB::table('flights')->insert([
                    'booking_id' => $booking->id,
                    'dep' => $booking->dep,
                    'arr' => $booking->arr,
                    'ctot' => $booking->ctot,
                    'eta' => $booking->eta,
                    'route' => $booking->route,
                    'oceanicFL' => $booking->oceanicFL,
                    'oceanicTrack' => $booking->oceanicTrack,
                ]);
            }
        });

        // Removing columns from bookings table that won't be used anymore
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['dep']);
            $table->dropForeign(['arr']);

            $table->dropColumn([
                'dep',
                'arr',
                'ctot',
                'eta',
                'route',
                'oceanicFL',
                'oceanicTrack'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Re-add old columns to bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('oceanicTrack', 2)->nullable()->after('selcal');
            $table->string('oceanicFL', 3)->nullable()->after('selcal');
            $table->string('route')->nullable()->after('selcal');
            $table->dateTime('eta')->nullable()->after('selcal');
            $table->dateTime('ctot')->nullable()->after('selcal');
            $table->unsignedInteger('arr')->after('selcal');
            $table->unsignedInteger('dep')->after('selcal');
        });

        // Re-add old values to bookings table
        DB::table('flights')->chunkById(100, function ($flights) {
            foreach ($flights as $flight) {
                DB::table('bookings')
                    ->where('id', $flight->booking_id)
                    ->update([
                        'dep' => $flight->dep,
                        'arr' => $flight->arr,
                        'ctot' => $flight->ctot,
                        'eta' => $flight->eta,
                        'route' => $flight->route,
                        'oceanicFL' => $flight->oceanicFL,
                        'oceanicTrack' => $flight->oceanicTrack,
                    ]);
            }
        });

        // Drop Re-add foreign keys for airports
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('dep')->references('id')->on('airports');
            $table->foreign('arr')->references('id')->on('airports');
        });
        Schema::dropIfExists('flights');
    }
}
