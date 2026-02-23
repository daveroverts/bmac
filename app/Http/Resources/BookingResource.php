<?php

namespace App\Http\Resources;

use App\Models\Flight;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Booking
 */
class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var Flight $flight */
        $flight = $this->flights()->first();
        return [
            'uuid' => $this->uuid,
            'event_id' => $this->event->id,
            'event_name' => $this->event->name,
            'user' => $this->user->id ?? null,
            // TODO: Remove when legacy unversioned API routes are dropped (2026-12-31)
            'full_name' => $this->when(! $request->is('api/v1/*'), $this->user->full_name ?? null),
            'status' => $this->status,
            'callsign' => $this->callsign,
            'acType' => $this->acType,
            'selcal' => $this->selcal,
            'dep' => $flight->airportDep->icao,
            'arr' => $flight->airportArr->icao,
            'ctot' => $flight->ctot ? $flight->ctot->format('Hi') . 'z' : null,
            'eta' => $flight->eta ? $flight->eta->format('Hi') . 'z' : null,
            'route' => $flight->route,
            'oceanicFL' => $flight->oceanicFL,
            'oceanicTrack' => $flight->oceanicTrack,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
            'links' => [
                'dep' => route('v1.airports.show', $flight->airportDep),
                'arr' => route('v1.airports.show', $flight->airportArr),
            ],
        ];
    }
}
