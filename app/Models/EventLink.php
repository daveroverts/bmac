<?php

namespace App\Models;

use App\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventLink extends Model
{
    use HasFactory;
    use LogsActivity;

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function event()
    {
        return $this->hasOne(Event::class);
    }

    // TODO Do we want to split this to a dedicated EventLinkType?
    // TODO Or do we want to create a LinkType model / collection?
    public function type()
    {
        return $this->hasOne(AirportLinkType::class);
    }
}
