<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class BookingStatus extends Enum
{
    public const UNASSIGNED = 0;
    public const RESERVED = 1;
    public const BOOKED = 2;
}
