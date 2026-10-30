<?php

namespace App\Rules;

use App\Models\Booking;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidSelcal implements ValidationRule
{
    /**
     * SELCAL 32 characters, from lowest to highest.
     */
    private const string CHARACTERS = 'ABCDEFGHJKLMPQRSTUVWXYZ123456789';

    /**
     * Create a new rule instance.
     */
    public function __construct(
        private readonly int $eventId,
        private readonly ?int $excludeBookingId = null,
    ) {
    }

    /**
     * Run the validation rule.
     *
     * Validates that the SELCAL:
     * - Matches the required format (XX-XX where X is A-Z or 1-9, except I, N and O)
     * - Contains unique characters
     * - Has characters in SELCAL order (A-Z, then 1-9) within each pair
     * - Is not already used in the same event
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The SELCAL must be a string.');

            return;
        }

        // Separate characters
        $char1 = substr($value, 0, 1);
        $char2 = substr($value, 1, 1);
        $char3 = substr($value, 3, 1);
        $char4 = substr($value, 4, 1);

        // Check if SELCAL has valid format
        if (! preg_match('/^[A-HJ-MP-Z1-9]{2}-[A-HJ-MP-Z1-9]{2}$/', $value)) {
            $fail('The SELCAL format is invalid. Must be XX-XX using valid SELCAL characters.');

            return;
        }

        // Check if each character is unique
        $chars = [$char1, $char2, $char3, $char4];
        if (count($chars) !== count(array_unique($chars))) {
            $fail('The SELCAL must contain unique characters.');

            return;
        }

        // Check if characters per pair are in SELCAL order
        if (strpos(self::CHARACTERS, $char1) > strpos(self::CHARACTERS, $char2)
            || strpos(self::CHARACTERS, $char3) > strpos(self::CHARACTERS, $char4)) {
            $fail('The SELCAL characters must be in SELCAL order (A-Z, then 1-9) within each pair.');

            return;
        }

        // Check for duplicates within the same event
        $query = Booking::where('event_id', $this->eventId)
            ->where('selcal', '=', $value);

        if ($this->excludeBookingId !== null) {
            $query->where('id', '!=', $this->excludeBookingId);
        }

        if ($query->exists()) {
            $fail('The SELCAL is already in use for this event.');

            return;
        }
    }
}
