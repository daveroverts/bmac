<?php

namespace App\Models;

use App\Models\Event;
use App\Models\AirportLinkType;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventLink extends Model
{
    use HasFactory;
    use LogsActivity;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // TODO Do we want to split this to a dedicated EventLinkType?
    // TODO Or do we want to create a LinkType model / collection?
    public function type()
    {
        return $this->belongsTo(AirportLinkType::class, 'event_link_type_id');
    }
}
