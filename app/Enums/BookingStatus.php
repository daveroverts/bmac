<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class BookingStatus extends Enum
{
    const UNASSIGNED = 0;
    const RESERVED = 1;
    const BOOKED = 2;
}
