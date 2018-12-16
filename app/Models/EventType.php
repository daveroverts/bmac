<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class EventType extends Model
{
    use LogsActivity;

    public $incrementing = false;
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name',
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function events()
    {
        return $this->belongsTo(Event::class, 'id');
    }
}
