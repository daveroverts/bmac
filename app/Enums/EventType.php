<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static ONEWAY()
 * @method static static CITYPAIR()
 * @method static static FLYIN()
 * @method static static GROUPFLIGHT()
 * @method static static MULTIFLIGHTS()
 */
final class EventType extends Enum
{
    public const ONEWAY = 1;
    public const CITYPAIR = 2;
    public const FLYIN = 3;
    public const GROUPFLIGHT = 4;
    public const MULTIFLIGHTS = 5;
}
