<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Booking;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id;
    }

    /**
     * Determine whether the user can create bookings.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the booking.
     */
    public function update(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id || empty($booking->user_id);
    }

    /**
     * Determine whether the user can delete the booking.
     */
    public function delete(User $user, Booking $booking): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the booking.
     */
    public function restore(User $user, Booking $booking): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the booking.
     */
    public function forceDelete(User $user, Booking $booking): bool
    {
        return false;
    }

    /**
     * Determine whether the user can cancel the booking.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id;
    }

    /**
     * Determine whether the user can reserve the booking.
     */
    public function reserve(User $user, Booking $booking): bool
    {
        return $booking->status === \App\Enums\BookingStatus::UNASSIGNED
            && $booking->event->startBooking <= now()
            && $booking->event->endBooking >= now();
    }

    /**
     * Determine whether the user can edit the booking.
     */
    public function edit(User $user, Booking $booking): bool
    {
        // Must own the booking
        if ($booking->user_id !== $user->id) {
            return false;
        }

        // Hard lock after endBooking
        if ($booking->event->endBooking < now()) {
            return false;
        }

        // Must be RESERVED or BOOKED to edit
        return in_array($booking->status, [\App\Enums\BookingStatus::RESERVED, \App\Enums\BookingStatus::BOOKED]);
    }
}
