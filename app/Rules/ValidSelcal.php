<?php

namespace App\Rules;

use App\Models\Booking;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidSelcal implements ValidationRule
{
    /**
     * Create a new rule instance.
     */
    public function __construct(
        private int $eventId
    ) {
    }

    /**
     * Run the validation rule.
     *
     * Validates that the SELCAL:
     * - Matches the required format (XX-XX where X is A-S except I, N, O, T)
     * - Contains unique characters
     * - Has characters in alphabetical order within each pair
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
        if (in_array(preg_match("/[ABCDEFGHJKLMPQRS]{2}[-][ABCDEFGHJKLMPQRS]{2}/", $value), [0, false], true)) {
            $fail('The SELCAL format is invalid. Must be XX-XX using valid SELCAL characters.');

            return;
        }

        // Check if each character is unique
        if (substr_count($value, $char1) > 1 || substr_count($value, $char2) > 1 || substr_count(
            $value,
            $char3
        ) > 1 || substr_count($value, $char4) > 1) {
            $fail('The SELCAL must contain unique characters.');

            return;
        }

        // Check if characters per pair are in alphabetical order
        if ($char1 > $char2 || $char3 > $char4) {
            $fail('The SELCAL characters must be in alphabetical order within each pair.');

            return;
        }

        // Check for duplicates within the same event
        if (Booking::where('event_id', $this->eventId)
            ->where('selcal', '=', $value)
            ->first()
        ) {
            $fail('The SELCAL is already in use for this event.');

            return;
        }
    }
}
