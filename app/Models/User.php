<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Notifications\Notifiable;
use App\Http\Controllers\OAuthController;
use League\OAuth2\Client\Token\AccessToken;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name_first
 * @property string $name_last
 * @property string $email
 * @property bool $isAdmin
 * @property int $airport_view
 * @property bool $use_monospace_font
 * @property string|null $remember_token
 * @property string|null $access_token
 * @property string|null $refresh_token
 * @property int|null $token_expires
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Booking[] $bookings
 * @property-read int|null $bookings_count
 * @property-read string $full_name
 * @property-read string $pic
 * @property-read \League\OAuth2\Client\Token\AccessToken|null $token
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAirportView($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNameFirst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereNameLast($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTokenExpires($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUseMonospaceFont($value)
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
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'isAdmin' => 'boolean',
        'use_monospace_font' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getFullNameAttribute(): string
    {
        return ucfirst($this->name_first) . ' ' . ucfirst($this->name_last);
    }

    public function getPicAttribute(): string
    {
        if (!empty($this->full_name) && !empty($this->id)) {
            return "{$this->full_name} | {$this->id}";
        }
        return '-';
    }

    public function getTokenAttribute(): ?AccessToken
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
            'access_token' => ($token) ? $token->getToken() : null,
            'refresh_token' => ($token) ? $token->getRefreshToken() : null,
            'token_expires' => ($token) ? $token->getExpires() : null,
        ]);

        return $token;
    }
}
