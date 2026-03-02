<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalog;

use App\Domain\Catalog\SpecializationWithLanguages;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SpecializationWithLanguages */
final class SpecializationWithLanguagesResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $spec = $this->resource->specialization;

        return [
            'spec_id' => $spec->id,
            'spec_key' => $spec->key,
            'spec_name' => $spec->name,
            'programming_languages' => ProgrammingLanguageResource::collection($this->resource->programmingLanguages),
        ];
    }
}
