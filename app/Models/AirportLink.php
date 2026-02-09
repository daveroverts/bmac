<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $airport_id
 * @property int $airportLinkType_id
 * @property string|null $name
 * @property string $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Airport|null $airport
 * @property-read \App\Models\AirportLinkType|null $type
 * @method static \Database\Factories\AirportLinkFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AirportLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AirportLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AirportLink query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AirportLink whereAirportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AirportLink whereAirportLinkTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AirportLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AirportLink whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AirportLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AirportLink whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AirportLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AirportLink whereUrl($value)
 * @mixin \Eloquent
 */
class AirportLink extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    public function airport(): HasOne
    {
        return $this->hasOne(Airport::class, 'id', 'airport_id');
    }

    public function type(): HasOne
    {
        return $this->hasOne(AirportLinkType::class, 'id', 'airportLinkType_id');
    }
}
