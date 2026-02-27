<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property ?string $middle_name
 * @property string $email
 * @property ?string $pending_email
 * @property string $unique_nickname
 * @property string $password
 * @property ?\DateTime $email_verified_at
 * @property ?string $remember_token
 * @property-read Collection<int, Mentor> $mentors
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    public function mentors(): HasMany
    {
        return $this->hasMany(Mentor::class);
    }

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'unique_nickname',
        'email',
        'pending_email',
        'password',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
