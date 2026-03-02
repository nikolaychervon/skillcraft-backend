<?php

declare(strict_types=1);

namespace App\Http\Resources\Mentor;

use App\Domain\Mentor\Track;
use App\Http\Resources\Catalog\SpecializationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Track */
final class TrackResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'key' => $this->key,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'specialization' => SpecializationResource::make($this->specialization),
            'programming_language' => SpecializationResource::make($this->programmingLanguage),
        ];
    }
}
