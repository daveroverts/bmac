<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Faq
 *
 * @property int $id
 * @property bool $is_online
 * @property string $question
 * @property string $answer
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Event[] $events
 * @property-read int|null $events_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Faq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Faq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Faq query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Faq whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Faq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Faq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Faq whereIsOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Faq whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Faq whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Faq extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_online' => 'boolean',
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function events()
    {
        return $this->belongsToMany(Event::class);
    }
}
