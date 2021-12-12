<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\AirportLink
 *
 * @property int $id
 * @property int $airport_id
 * @property int $airportLinkType_id
 * @property string|null $name
 * @property string $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Airport|null $airport
 * @property-read \App\Models\AirportLinkType|null $type
 * @method static \Database\Factories\AirportLinkFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLink query()
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLink whereAirportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLink whereAirportLinkTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLink whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLink whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLink whereUrl($value)
 * @mixin \Eloquent
 */
class AirportLink extends Model
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

    public function airport()
    {
        return $this->hasOne(Airport::class, 'id', 'airport_id');
    }

    // TODO Or do we want to create a LinkType model / collection?
    public function type()
    {
        return $this->hasOne(AirportLinkType::class, 'id', 'airportLinkType_id');
    }
}
