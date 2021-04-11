<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\AirportLinkType
 *
 * @property int $id
 * @property string $name
 * @property string|null $class
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\AirportLink $links
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLinkType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLinkType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLinkType query()
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLinkType whereClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLinkType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLinkType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLinkType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirportLinkType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AirportLinkType extends Model
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

    public function links()
    {
        return $this->belongsTo(AirportLink::class, 'id');
    }
}
