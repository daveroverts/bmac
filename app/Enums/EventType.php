<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class EventType extends Enum
{
    const ONEWAY = 1;
    const CITYPAIR = 2;
    const FLYIN = 3;
    const GROUPFLIGHT = 4;
}
