<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Event> $events
 * @property-read int|null $events_count
 * @method static Builder<static>|EventType newModelQuery()
 * @method static Builder<static>|EventType newQuery()
 * @method static Builder<static>|EventType query()
 * @method static Builder<static>|EventType whereId($value)
 * @method static Builder<static>|EventType whereName($value)
 * @mixin \Eloquent
 */
class EventType extends Model
{
    use LogsActivity;

    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = ['id'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    #[\Override]
    protected static function booted()
    {
        static::addGlobalScope('order', function (Builder $builder): void {
            $builder->orderBy('name');
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
