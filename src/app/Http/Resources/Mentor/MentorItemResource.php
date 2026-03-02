<?php

declare(strict_types=1);

namespace App\Http\Resources\Mentor;

use App\Domain\Mentor\Mentor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Mentor */
final class MentorItemResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'target_level' => $this->targetLevel,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'track' => TrackResource::make($this->track),
        ];
    }
}
