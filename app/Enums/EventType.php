<?php

namespace App\Enums;

enum EventType: int
{
    case ONEWAY = 1;
    case CITYPAIR = 2;
    case FLYIN = 3;
    case GROUPFLIGHT = 4;
    case MULTIFLIGHTS = 5;
}
