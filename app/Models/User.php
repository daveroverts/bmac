<?php

namespace App\Models;

use App\Enums\AirportView;
use Spatie\Activitylog\LogOptions;
use Illuminate\Notifications\Notifiable;
use App\Http\Controllers\OAuthController;
use League\OAuth2\Client\Token\AccessToken;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
 * @property-read \League\OAuth2\Client\Token\AccessToken|null $token
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
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    protected function getFullNameAttribute(): string
    {
        return ucfirst($this->name_first) . ' ' . ucfirst($this->name_last);
    }

    protected function getPicAttribute(): string
    {
        if (!empty($this->full_name) && !empty($this->id)) {
            return sprintf('%s | %s', $this->full_name, $this->id);
        }

        return '-';
    }

    protected function getTokenAttribute(): ?AccessToken
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
            $token = OAuthController::updateToken($token);
        }

        // Can't put it inside the "if token expired"; $this is null there
        // but anyway Laravel will only update if any changes have been made.
        $this->update([
            'access_token' => ($token instanceof \League\OAuth2\Client\Token\AccessToken) ? $token->getToken() : null,
            'refresh_token' => ($token instanceof \League\OAuth2\Client\Token\AccessToken) ? $token->getRefreshToken() : null,
            'token_expires' => ($token instanceof \League\OAuth2\Client\Token\AccessToken) ? $token->getExpires() : null,
        ]);

        return $token;
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'isAdmin' => 'boolean',
            'use_monospace_font' => 'boolean',
            'airport_view' => AirportView::class,
        ];
    }
}
