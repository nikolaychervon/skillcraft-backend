<?php

declare(strict_types=1);

namespace Tests\Unit\Profile;

use App\Application\User\Profile\VerifyEmailChange;
use App\Domain\User\Exceptions\Email\InvalidConfirmationLinkException;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\User;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class VerifyEmailChangeActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_confirms_pending_email_when_hash_matches(): void
    {
        $user = new User(
            id: 1,
            email: 'old@example.com',
            password: 'hashed',
            firstName: 'John',
            lastName: 'Doe',
            uniqueNickname: 'johndoe',
            middleName: null,
            pendingEmail: 'new@example.com',
            emailVerifiedAt: new DateTimeImmutable(),
        );
        $hash = hash('sha256', 'new@example.com');

        $repo = Mockery::mock(UserRepositoryInterface::class);
        $repo->shouldReceive('findById')->once()->with(1)->andReturn($user);
        $repo->shouldReceive('confirmPendingEmail')->once()->with(Mockery::on(
            fn (User $u): bool => $u->id === 1 && $u->pendingEmail === 'new@example.com'
        ));

        $action = new VerifyEmailChange($repo);
        $action->run(1, $hash);
    }

    public function test_it_throws_user_not_found_when_user_does_not_exist(): void
    {
        $repo = Mockery::mock(UserRepositoryInterface::class);
        $repo->shouldReceive('findById')->once()->with(999)->andReturn(null);
        $repo->shouldNotReceive('confirmPendingEmail');

        $action = new VerifyEmailChange($repo);

        $this->expectException(UserNotFoundException::class);

        $action->run(999, hash('sha256', 'any@example.com'));
    }

    public function test_it_throws_invalid_link_when_no_pending_email(): void
    {
        $user = new User(
            id: 1,
            email: 'old@example.com',
            password: 'hashed',
            firstName: 'John',
            lastName: 'Doe',
            uniqueNickname: 'johndoe',
            middleName: null,
            pendingEmail: null,
            emailVerifiedAt: new DateTimeImmutable(),
        );

        $repo = Mockery::mock(UserRepositoryInterface::class);
        $repo->shouldReceive('findById')->once()->with(1)->andReturn($user);
        $repo->shouldNotReceive('confirmPendingEmail');

        $action = new VerifyEmailChange($repo);

        $this->expectException(InvalidConfirmationLinkException::class);

        $action->run(1, hash('sha256', 'new@example.com'));
    }

    public function test_it_throws_invalid_link_when_hash_does_not_match(): void
    {
        $user = new User(
            id: 1,
            email: 'old@example.com',
            password: 'hashed',
            firstName: 'John',
            lastName: 'Doe',
            uniqueNickname: 'johndoe',
            middleName: null,
            pendingEmail: 'new@example.com',
            emailVerifiedAt: new DateTimeImmutable(),
        );

        $repo = Mockery::mock(UserRepositoryInterface::class);
        $repo->shouldReceive('findById')->once()->with(1)->andReturn($user);
        $repo->shouldNotReceive('confirmPendingEmail');

        $action = new VerifyEmailChange($repo);

        $this->expectException(InvalidConfirmationLinkException::class);

        $action->run(1, hash('sha256', 'wrong@example.com'));
    }
}
