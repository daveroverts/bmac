<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $name
 * @property string|null $class
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AirportLink> $links
 * @property-read int|null $links_count
 * @method static Builder<static>|AirportLinkType newModelQuery()
 * @method static Builder<static>|AirportLinkType newQuery()
 * @method static Builder<static>|AirportLinkType query()
 * @method static Builder<static>|AirportLinkType whereClass($value)
 * @method static Builder<static>|AirportLinkType whereCreatedAt($value)
 * @method static Builder<static>|AirportLinkType whereId($value)
 * @method static Builder<static>|AirportLinkType whereName($value)
 * @method static Builder<static>|AirportLinkType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AirportLinkType extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $guarded = ['id'];

    public const string CACHE_KEY_DROPDOWN = 'airport_link_types.dropdown';

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
     * Get airport link types formatted for dropdown selects, cached for performance.
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

    public function links(): HasMany
    {
        return $this->hasMany(AirportLink::class, 'airportLinkType_id');
    }
}
