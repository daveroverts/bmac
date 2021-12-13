<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Airport
 */
class AirportResource extends JsonResource
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
            'icao' => $this->icao,
            'iata' => $this->iata,
            'name' => $this->name,
            'links' => new AirportLinksCollection($this->links)
        ];
    }
}
