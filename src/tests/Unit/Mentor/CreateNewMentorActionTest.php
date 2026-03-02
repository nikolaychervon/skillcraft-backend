<?php

declare(strict_types=1);

namespace Tests\Unit\Mentor;

use App\Domain\Mentor\Exceptions\TrackNotFoundException;
use App\Domain\Mentor\Mentor;
use App\Domain\Mentor\Repositories\MentorRepositoryInterface;
use App\Domain\Mentor\Repositories\TrackRepositoryInterface;
use App\Domain\Mentor\RequestData\CreateNewMentorRequestData;
use App\Application\Mentor\CreateNewMentor;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class CreateNewMentorActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_creates_mentor_when_track_exists(): void
    {
        $track = MentorTestFactory::createTrack(1);
        $mentor = MentorTestFactory::createMentor(1, 10, 'New Mentor', 'new-mentor');
        $requestData = new CreateNewMentorRequestData(
            specializationId: 1,
            programmingLanguageId: 1,
            name: 'New Mentor',
            slug: 'new-mentor',
            useNameToCallMe: true,
            targetLevel: 'Middle',
            mentorPersona: 'friendly',
            howToCallMe: null,
        );

        $trackRepo = Mockery::mock(TrackRepositoryInterface::class);
        $trackRepo->shouldReceive('getBySpecializationAndLanguage')
            ->once()
            ->with(1, 1)
            ->andReturn($track);

        $mentorRepo = Mockery::mock(MentorRepositoryInterface::class);
        $mentorRepo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data): bool {
                return $data['user_id'] === 10
                    && $data['track_id'] === 1
                    && $data['name'] === 'New Mentor'
                    && $data['slug'] === 'new-mentor'
                    && $data['target_level'] === 'Middle'
                    && $data['mentor_persona'] === 'friendly'
                    && $data['use_name_to_call_me'] === true;
            }))
            ->andReturn($mentor);

        $action = new CreateNewMentor($mentorRepo, $trackRepo);
        $result = $action->run($requestData, 10);

        $this->assertSame($mentor, $result);
    }

    public function test_it_throws_track_not_found_when_track_does_not_exist(): void
    {
        $requestData = new CreateNewMentorRequestData(
            specializationId: 1,
            programmingLanguageId: 99,
            name: 'Mentor',
            slug: 'mentor',
            useNameToCallMe: true,
            targetLevel: 'Middle',
            mentorPersona: 'friendly',
        );

        $trackRepo = Mockery::mock(TrackRepositoryInterface::class);
        $trackRepo->shouldReceive('getBySpecializationAndLanguage')
            ->once()
            ->with(1, 99)
            ->andReturn(null);

        $mentorRepo = Mockery::mock(MentorRepositoryInterface::class);
        $mentorRepo->shouldNotReceive('create');

        $action = new CreateNewMentor($mentorRepo, $trackRepo);

        $this->expectException(TrackNotFoundException::class);
        $action->run($requestData, 10);
    }
}
