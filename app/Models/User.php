<?php

namespace App\Models;

use App\Enums\AirportView;
use App\Services\OAuth\VatsimProvider;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use League\OAuth2\Client\Token\AccessToken;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $name_first
 * @property string $name_last
 * @property string $email
 * @property bool $isAdmin
 * @property AirportView $airport_view
 * @property bool $use_monospace_font
 * @property string|null $remember_token
 * @property string|null $access_token
 * @property string|null $refresh_token
 * @property int|null $token_expires
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking> $bookings
 * @property-read int|null $bookings_count
 * @property-read string $full_name
 * @property-read string $pic
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAirportView($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNameFirst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNameLast($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTokenExpires($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUseMonospaceFont($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasFactory;
    use LogsActivity;
    use Notifiable;

    public $incrementing = false;

    protected $guarded = [
        'isAdmin'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
        'access_token',
        'refresh_token',
        'token_expires',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    /** @return HasMany<Booking, $this> */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): string => ucfirst((string) ($attributes['name_first'] ?? '')) . ' ' . ucfirst((string) ($attributes['name_last'] ?? '')),
        );
    }

    protected function pic(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes): string {
                if (! empty($this->full_name) && ! empty($attributes['id'])) {
                    return sprintf('%s | %s', $this->full_name, $attributes['id']);
                }

                return '-';
            },
        );
    }

    /**
     * Returns a valid access token, refreshing it via the OAuth provider if it has expired.
     * Persists updated token fields to the database when a refresh occurs.
     */
    public function refreshTokenIfExpired(): ?AccessToken
    {
        if ($this->access_token === null) {
            return null;
        }

        $token = new AccessToken([
            'access_token' => $this->access_token,
            'refresh_token' => $this->refresh_token,
            'expires' => $this->token_expires,
        ]);

        if ($token->hasExpired()) {
            $refreshedToken = resolve(VatsimProvider::class)->updateToken($token);
            $token = $refreshedToken instanceof AccessToken ? $refreshedToken : null;

            $this->update([
                'access_token' => $token?->getToken(),
                'refresh_token' => $token?->getRefreshToken(),
                'token_expires' => $token?->getExpires(),
            ]);
        }

        return $token;
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'isAdmin' => 'boolean',
            'use_monospace_font' => 'boolean',
            'airport_view' => AirportView::class,
        ];
    }
}
