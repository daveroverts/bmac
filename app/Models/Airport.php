<?php

namespace App\Models;

use App\Enums\AirportView;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Airport
 *
 * @property int $id
 * @property string $icao
 * @property string $iata
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Event[] $eventArr
 * @property-read int|null $event_arr_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Event[] $eventDep
 * @property-read int|null $event_dep_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Flight[] $flightsArr
 * @property-read int|null $flights_arr_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Flight[] $flightsDep
 * @property-read int|null $flights_dep_count
 * @property-read mixed $full_name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AirportLink[] $links
 * @property-read int|null $links_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereIata($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereIcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Airport whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Airport extends Model
{
    use HasFactory;
    use LogsActivity;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function flightsDep()
    {
        return $this->hasMany(Flight::class, 'dep');
    }

    public function flightsArr()
    {
        return $this->hasMany(Flight::class, 'arr');
    }

    public function eventDep()
    {
        return $this->hasMany(Event::class, 'dep');
    }

    public function eventArr()
    {
        return $this->hasMany(Event::class, 'arr');
    }

    public function links()
    {
        return $this->hasMany(AirportLink::class);
    }

    public function getFullNameAttribute()
    {
        if (!$this->id) {
            return '-';
        }
        if (auth()->check() && auth()->user()->airport_view !== AirportView::NAME) {
            switch (auth()->user()->airport_view) {
                case AirportView::ICAO:
                    return '<abbr title="' . $this->name . ' | [' . $this->iata . ']">' . $this->icao . '</abbr>';
                    break;
                case AirportView::IATA:
                    return '<abbr title="' . $this->name . ' | [' . $this->icao . ']">' . $this->iata . '</abbr>';
                    break;
            }
        }
        return '<abbr title="' . $this->icao . ' | [' . $this->iata . ']">' . $this->name . '</abbr>';
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'icao';
    }
}
