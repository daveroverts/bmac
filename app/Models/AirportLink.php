<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class AirportLink extends Model
{
    use LogsActivity;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function airport()
    {
        return $this->hasOne(Airport::class, 'id', 'airport_id');
    }

    public function type()
    {
        return $this->hasOne(AirportLinkType::class, 'id', 'airportLinkType_id');
    }
}
