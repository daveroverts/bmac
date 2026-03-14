<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

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

    public const string CACHE_KEY_DROPDOWN = 'event_types.dropdown';

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

        static::saved(fn () => Cache::forget(self::CACHE_KEY_DROPDOWN));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY_DROPDOWN));
    }

    /**
     * Get event types formatted for dropdown selects, cached for performance.
     *
     * @return Collection<string, string>
     */
    public static function forDropdown(): Collection
    {
        return Cache::remember(self::CACHE_KEY_DROPDOWN, now()->addHour(), fn (): Collection => static::pluck('name', 'id'));
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
