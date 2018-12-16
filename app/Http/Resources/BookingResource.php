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
            'event' => $this->event->name,
            'user' => new UserResource($this->user),
            'status' => $this->status,
            'callsign' => $this->callsign,
            'acType' => $this->acType,
            'selcal' => $this->selcal,
            'dep' => new AirportResource($this->airportDep),
            'arr' => new AirportResource($this->airportArr),
            'ctot' => $this->ctot,
            'eta' => $this->eta,
            'route' => $this->route,
            'oceanicFL' => $this->oceanicFL,
            'oceanicTrack' => $this->oceanicTrack,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at
        ];
    }
}
