<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class EventType extends Enum
{
    const OneWay = 1;
    const CityPair = 2;
    const FlyIn = 3;
}
