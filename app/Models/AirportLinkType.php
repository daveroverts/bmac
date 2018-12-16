<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class AirportLinkType extends Model
{
    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'class',
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function links()
    {
        return $this->belongsTo(AirportLink::class, 'id');
    }
}
