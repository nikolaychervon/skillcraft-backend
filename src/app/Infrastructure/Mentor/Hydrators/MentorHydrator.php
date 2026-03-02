<?php

declare(strict_types=1);

namespace App\Infrastructure\Mentor\Hydrators;

use App\Domain\Mentor\Mentor;
use DateTimeImmutable;
use Illuminate\Support\Collection;

final class MentorHydrator
{
    public function __construct(
        private TrackHydrator $trackHydrator,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(Mentor $mentor): array
    {
        return [
            'id' => $mentor->id,
            'user_id' => $mentor->userId,
            'name' => $mentor->name,
            'slug' => $mentor->slug,
            'target_level' => $mentor->targetLevel,
            'current_level' => $mentor->currentLevel,
            'how_to_call_me' => $mentor->howToCallMe,
            'use_name_to_call_me' => $mentor->useNameToCallMe,
            'mentor_persona' => $mentor->mentorPersona,
            'created_at' => $mentor->createdAt->format('Y-m-d H:i:s'),
            'track' => $this->trackHydrator->toArray($mentor->track),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function fromArray(array $data): Mentor
    {
        return new Mentor(
            id: $data['id'],
            userId: $data['user_id'],
            name: $data['name'],
            slug: $data['slug'],
            targetLevel: $data['target_level'],
            currentLevel: $data['current_level'] ?? null,
            howToCallMe: $data['how_to_call_me'] ?? '',
            useNameToCallMe: $data['use_name_to_call_me'],
            mentorPersona: $data['mentor_persona'],
            createdAt: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['created_at']),
            track: $this->trackHydrator->fromArray($data['track']),
        );
    }

    /**
     * @param Collection<int, Mentor> $mentors
     * @return array<int, array<string, mixed>>
     */
    public function toArrayCollection(Collection $mentors): array
    {
        return $mentors->map(fn (Mentor $m): array => $this->toArray($m))->all();
    }

    /**
     * @param array<int, array<string, mixed>> $data
     * @return Collection<int, Mentor>
     */
    public function fromArrayCollection(array $data): Collection
    {
        return collect($data)->map(fn (array $item): Mentor => $this->fromArray($item));
    }
}
