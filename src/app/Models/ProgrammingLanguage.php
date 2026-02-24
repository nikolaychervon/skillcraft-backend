<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $key
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, Track> $tracks
 */
class ProgrammingLanguage extends Model
{
    protected $fillable = ['key', 'name'];

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }
}
