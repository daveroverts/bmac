@props(['booking', 'orderBy'])

@php
    $flight = $booking->flights->where('order_by', $orderBy)->first();
@endphp

@if ($flight)
    <abbr title="{{ $flight->airportDep->name }} | [{{ $flight->airportDep->iata }}]">{{ $flight->airportDep->icao }}</abbr> - <abbr title="{{ $flight->airportArr->name }} | [{{ $flight->airportArr->iata }}]">{{ $flight->airportArr->icao }}</abbr> {{ $flight->formatted_ctot }}
@else
    -
@endif
