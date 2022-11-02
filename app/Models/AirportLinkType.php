<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
 * @property-read AirportLink $links
 * @method static Builder|AirportLinkType newModelQuery()
 * @method static Builder|AirportLinkType newQuery()
 * @method static Builder|AirportLinkType query()
 * @method static Builder|AirportLinkType whereClass($value)
 * @method static Builder|AirportLinkType whereCreatedAt($value)
 * @method static Builder|AirportLinkType whereId($value)
 * @method static Builder|AirportLinkType whereName($value)
 * @method static Builder|AirportLinkType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AirportLinkType extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $guarded = ['id'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('name');
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    public function links(): BelongsTo
    {
        return $this->belongsTo(AirportLink::class, 'id');
    }
}
