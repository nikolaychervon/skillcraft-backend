<?php

declare(strict_types=1);

namespace Tests\Unit\Mentor;

use App\Domain\Catalog\ProgrammingLanguage;
use App\Domain\Catalog\Specialization;
use App\Domain\Mentor\Mentor;
use App\Domain\Mentor\Track;
use DateTimeImmutable;

final class MentorTestFactory
{
    public static function createTrack(int $id = 1, string $specKey = 'backend', string $langKey = 'php'): Track
    {
        return new Track(
            id: $id,
            key: $specKey . '_' . $langKey,
            name: ucfirst($langKey) . ' ' . ucfirst($specKey),
            createdAt: new DateTimeImmutable('2024-01-01 00:00:00'),
            specialization: new Specialization($id, $specKey, ucfirst($specKey)),
            programmingLanguage: new ProgrammingLanguage($id, $langKey, ucfirst($langKey)),
        );
    }

    public static function createMentor(
        int $id = 1,
        int $userId = 1,
        string $name = 'Test Mentor',
        string $slug = 'test-mentor',
    ): Mentor {
        return new Mentor(
            id: $id,
            userId: $userId,
            name: $name,
            slug: $slug,
            targetLevel: 'Middle',
            currentLevel: null,
            howToCallMe: '',
            useNameToCallMe: true,
            mentorPersona: 'friendly',
            createdAt: new DateTimeImmutable('2024-01-01 00:00:00'),
            track: self::createTrack(1),
        );
    }
}
