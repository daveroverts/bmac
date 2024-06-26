<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 *
 *
 * @property int $id
 * @property int $event_id
 * @property int $event_link_type_id
 * @property string|null $name
 * @property string $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Event $event
 * @property-read \App\Models\AirportLinkType $type
 * @method static \Database\Factories\EventLinkFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|EventLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventLink query()
 * @method static \Illuminate\Database\Eloquent\Builder|EventLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventLink whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventLink whereEventLinkTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventLink whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventLink whereUrl($value)
 * @mixin \Eloquent
 */
class EventLink extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AirportLinkType::class, 'event_link_type_id');
    }
}
