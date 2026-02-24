<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $track_id
 * @property string $name
 * @property string $slug
 * @property string $target_level
 * @property string $current_level
 * @property string|null $how_to_call_me
 * @property bool $use_name_to_call_me
 * @property string $mentor_persona
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Track $track
 */
class Mentor extends Model
{
    protected $fillable = [
        'user_id',
        'track_id',
        'name',
        'slug',
        'target_level',
        'current_level',
        'how_to_call_me',
        'use_name_to_call_me',
        'mentor_persona',
    ];

    protected $casts = [
        'use_name_to_call_me' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
