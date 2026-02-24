<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $key
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, Track> $tracks
 * @property-read Collection<int, ProgrammingLanguage> $programmingLanguages
 */
class Specialization extends Model
{
    protected $fillable = ['key', 'name'];

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }

    /**
     * Языки программирования, доступные в этой специализации (через треки).
     *
     * @return HasManyThrough<ProgrammingLanguage, Track>
     */
    public function programmingLanguages(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProgrammingLanguage::class,
            Track::class,
            'specialization_id',
            'id',
            'id',
            'programming_language_id'
        );
    }
}
