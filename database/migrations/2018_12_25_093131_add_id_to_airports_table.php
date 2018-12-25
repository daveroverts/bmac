<?php

use App\Models\Airport;
use App\Models\AirportLink;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIdToAirportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop foreign key, and rename column to _old
        Schema::table('airport_links', function (Blueprint $table) {
            $table->dropForeign(['icao_airport']);
            $table->renameColumn('icao_airport', 'icao_airport_old');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['dep']);
            $table->dropForeign(['arr']);
            $table->renameColumn('dep', 'dep_old');
            $table->renameColumn('arr', 'arr_old');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['dep']);
            $table->dropForeign(['arr']);
            $table->renameColumn('dep', 'dep_old');
            $table->renameColumn('arr', 'arr_old');
        });

        // Add new column, based on the previous names, this time, as unisigned int
        Schema::table('airport_links', function (Blueprint $table) {
            $table->unsignedInteger('airport_id')->after('id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedInteger('arr')->after('selcal');
            $table->unsignedInteger('dep')->after('selcal');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->unsignedInteger('arr')->after('description');
            $table->unsignedInteger('dep')->after('description');
        });


        // Remove the primary key from ICAO
        Schema::table('airports', function (Blueprint $table) {
            $table->dropPrimary();
        });

        // Add new id, and make that the primary key. Also add timestamps
        Schema::table('airports', function (Blueprint $table) {
            $table->increments('id')->first();
            $table->timestamps();
        });

        // Insert the new id's
        foreach (AirportLink::all() as $airportLink) {
            $airport = Airport::where('icao', $airportLink->icao_airport_old)->first();
            $airportLink->airport_id = $airport->id;
            $airportLink->save();
        }

        foreach (Booking::all() as $booking) {
            $airportDep = Airport::where('icao', $booking->dep_old)->first();
            $airportArr = Airport::where('icao', $booking->arr_old)->first();
            $booking->fill([
                'dep' => $airportDep->id,
                'arr' => $airportArr->id,
            ])->save();
        }

        foreach (Event::all() as $event) {
            $airportDep = Airport::where('icao', $event->dep_old)->first();
            $airportArr = Airport::where('icao', $event->arr_old)->first();
            $event->fill([
                'dep' => $airportDep->id,
                'arr' => $airportArr->id,
            ])->save();
        }

        // Drop old columns, and create new foreign keys
        Schema::table('airport_links', function (Blueprint $table) {
            $table->dropColumn('icao_airport_old');
            $table->foreign('airport_id')->references('id')->on('airports')->onDelete('cascade');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['dep_old', 'arr_old']);
            $table->foreign('dep')->references('id')->on('airports');
            $table->foreign('arr')->references('id')->on('airports');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['dep_old', 'arr_old']);
            $table->foreign('dep')->references('id')->on('airports');
            $table->foreign('arr')->references('id')->on('airports');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop foreign key, and rename column to _old
        Schema::table('airport_links', function (Blueprint $table) {
            $table->dropForeign(['airport_id']);
            $table->renameColumn('airport_id', 'airport_id_old');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['dep']);
            $table->dropForeign(['arr']);
            $table->renameColumn('dep', 'dep_old');
            $table->renameColumn('arr', 'arr_old');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['dep']);
            $table->dropForeign(['arr']);
            $table->renameColumn('dep', 'dep_old');
            $table->renameColumn('arr', 'arr_old');
        });

        // Add new column, based on the previous names
        Schema::table('airport_links', function (Blueprint $table) {
            $table->string('icao_airport')->after('id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('arr')->after('selcal');
            $table->string('dep')->after('selcal');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('arr')->after('description');
            $table->string('dep')->after('description');
        });

        // Remove the primary key from id. Drop timestamps
        Schema::table('airports', function (Blueprint $table) {
            $table->dropPrimary();
            $table->unsignedInteger('id')->change();
            $table->dropTimestamps();
        });

        // Make icao primary
        Schema::table('airports', function (Blueprint $table) {
            $table->primary('icao');
        });

        // Insert the new id's
        foreach (AirportLink::all() as $airportLink) {
            $airport = Airport::where('id', $airportLink->icao_airport_old)->first();
            $airportLink->icao_airport = $airport->icao;
            $airportLink->save();
        }

        foreach (Booking::all() as $booking) {
            $airportDep = Airport::where('id', $booking->dep_old)->first();
            $airportArr = Airport::where('id', $booking->arr_old)->first();
            $booking->fill([
                'dep' => $airportDep->icao,
                'arr' => $airportArr->icao,
            ])->save();
        }

        foreach (Event::all() as $event) {
            $airportDep = Airport::where('id', $event->dep_old)->first();
            $airportArr = Airport::where('id', $event->arr_old)->first();
            $event->fill([
                'dep' => $airportDep->icao,
                'arr' => $airportArr->icao,
            ])->save();
        }

        // Drop id
        Schema::table('airports', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        // Drop old columns, and create new foreign keys
        Schema::table('airport_links', function (Blueprint $table) {
            $table->dropColumn('airport_id_old');
            $table->foreign('icao_airport')->references('icao')->on('airports')->onDelete('cascade');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['dep_old', 'arr_old']);
            $table->foreign('dep')->references('icao')->on('airports');
            $table->foreign('arr')->references('icao')->on('airports');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['dep_old', 'arr_old']);
            $table->foreign('dep')->references('icao')->on('airports');
            $table->foreign('arr')->references('icao')->on('airports');
        });
    }
}
