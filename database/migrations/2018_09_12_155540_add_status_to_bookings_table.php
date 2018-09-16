<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->tinyInteger('status')->unsigned()->after('event_id')->default(BookingStatus::Unassigned);
            $table->unsignedInteger('user_id')->after('event_id')->nullable();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });

        foreach (Booking::all() as $booking) {
            if ($booking->bookedBy_id) {
                $booking->user_id = $booking->bookedBy_id;
                $booking->status = BookingStatus::Booked;
            }
            $booking->save();
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['reservedBy_id']);
            $table->dropForeign(['bookedBy_id']);
            $table->dropColumn(['reservedBy_id', 'bookedBy_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedInteger('bookedBy_id')->nullable()->after('user_id');
            $table->unsignedInteger('reservedBy_id')->nullable()->after('user_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('reservedBy_id')->references('id')->on('users');
            $table->foreign('bookedBy_id')->references('id')->on('users');
        });

        foreach (Booking::whereNotNull('user_id')->get() as $booking) {
            $booking->bookedBy_id = $booking->user_id;
            $booking->save();
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'status']);
        });
    }
}
