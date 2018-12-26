<?php

namespace App\Http\Resources;

use App\Models\Airport;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'id' => $this->id,
            'event_type' => $this->type->name,
            'name' => $this->name,
            'slug' => $this->slug,
            'image_url' => url($this->image_url),
            'description' => $this->description,
            'dep' => $this->airportDep->icao,
            'arr' => $this->airportArr->icao,
            'startEvent' => (string) $this->startEvent,
            'endEvent' => (string) $this->endEvent,
            'startBooking' => (string) $this->startBooking,
            'endBooking' => (string) $this->endBooking,
            'import_only' => (bool) $this->import_only,
            'uses_times' => (bool) $this->uses_times,
            'multiple_bookings_allowed' => (bool) $this->multiple_bookings_allowed,
            'is_oceanic_event' => (bool) $this->oceanic_event,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
            'links' => [
                'bookings' => url('api/events/' . $this->slug . '/bookings'),
                'dep' => url('api/airports/' . $this->airportDep->icao),
                'arr' => url('api/airports/' . $this->airportArr->icao),
            ]
        ];
    }
}
