<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $key
 * @property int $specialization_id
 * @property int $programming_language_id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Specialization $specialization
 * @property-read ProgrammingLanguage $programmingLanguage
 * @property-read Collection<int, Mentor> $mentors
 */
class Track extends Model
{
    protected $fillable = ['key', 'specialization_id', 'programming_language_id', 'name'];

    public function mentors(): HasMany
    {
        return $this->hasMany(Mentor::class);
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class);
    }

    public function programmingLanguage(): BelongsTo
    {
        return $this->belongsTo(ProgrammingLanguage::class);
    }
}
