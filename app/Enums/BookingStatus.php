<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static UNASSIGNED()
 * @method static static RESERVED()
 * @method static static BOOKED()
 */
final class BookingStatus extends Enum
{
    public const UNASSIGNED = 0;
    public const RESERVED = 1;
    public const BOOKED = 2;
}
