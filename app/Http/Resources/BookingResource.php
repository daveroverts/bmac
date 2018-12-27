<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'uuid' => $this->uuid,
            'event_id' => $this->event->id,
            'event_name' => $this->event->name,
            'user' => $this->user->id ?? null,
            'full_name' => $this->user->full_name ?? null,
            'status' => $this->status,
            'callsign' => $this->callsign,
            'acType' => $this->acType,
            'selcal' => $this->selcal,
            'dep' => $this->airportDep->icao,
            'arr' => $this->airportArr->icao,
            'ctot' => $this->ctot,
            'eta' => $this->eta,
            'route' => $this->route,
            'oceanicFL' => $this->oceanicFL,
            'oceanicTrack' => $this->oceanicTrack,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
            'links' => [
                'user' => url('/api/users/' . $this->user->id),
                'dep' => url('/api/airports/' . $this->airportDep->icao),
                'arr' => url('/api/airports/' . $this->airportArr->icao),
            ],
        ];
    }
}
