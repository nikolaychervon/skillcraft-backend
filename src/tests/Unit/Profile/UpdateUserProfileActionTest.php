<?php

namespace Tests\Unit\Profile;

use App\Application\User\Profile\UpdateUserProfile;
use App\Domain\User\Profile\RequestData\UpdateUserProfileRequestData;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\User;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class UpdateUserProfileActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_updates_all_profile_fields(): void
    {
        $repo = Mockery::mock(UserRepositoryInterface::class);
        $action = new UpdateUserProfile($repo);

        $user = new User(
            id: 1,
            email: 'u@u.com',
            password: 'hash',
            firstName: 'Old',
            lastName: 'Name',
            uniqueNickname: 'old_nick',
        );
        $requestData = new UpdateUserProfileRequestData(
            firstName: 'Иван',
            lastName: 'Петров',
            middleName: null,
            uniqueNickname: 'ivan_dev',
        );

        $updatedUser = new User(
            id: 1,
            email: 'u@u.com',
            password: 'hash',
            firstName: 'Иван',
            lastName: 'Петров',
            uniqueNickname: 'ivan_dev',
            middleName: null,
        );

        $repo->shouldReceive('update')
            ->once()
            ->with($user, [
                'first_name' => 'Иван',
                'last_name' => 'Петров',
                'middle_name' => null,
                'unique_nickname' => 'ivan_dev',
            ])
            ->andReturn($updatedUser);

        $result = $action->run($user, $requestData);
        $this->assertSame($updatedUser, $result);
    }
}
