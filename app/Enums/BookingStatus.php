<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class BookingStatus extends Enum
{
    const Unassigned = 0;
    const Reserved = 1;
    const Booked = 2;
}
