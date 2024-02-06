<?php

namespace App\Enums;

enum AirportView: int
{
    case NAME = 0;
    case ICAO = 1;
    case IATA = 2;
}
