<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AirportLinkType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'class',
    ];

    public function link()
    {
        return $this->belongsToMany(AirportLink::class, 'airport_links');
    }
}
