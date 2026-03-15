@props(['airport' => null])

@if (! $airport?->id)
    -
@elseif (auth()->check() && auth()->user()->airport_view === \App\Enums\AirportView::ICAO)
    <abbr title="{{ $airport->name }} | [{{ $airport->iata }}]">{{ $airport->icao }}</abbr>
@elseif (auth()->check() && auth()->user()->airport_view === \App\Enums\AirportView::IATA)
    <abbr title="{{ $airport->name }} | [{{ $airport->icao }}]">{{ $airport->iata }}</abbr>
@else
    <abbr title="{{ $airport->icao }} | [{{ $airport->iata }}]">{{ $airport->name }}</abbr>
@endif
