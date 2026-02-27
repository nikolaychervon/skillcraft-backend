<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Application\User\Auth\CreateNewUser;
use App\Application\User\Auth\ResendVerificationEmail;
use App\Domain\User\Auth\RequestData\CreatingUserRequestData;
use App\Domain\User\Auth\RequestData\ResendEmailRequestData;
use App\Domain\User\Exceptions\Email\EmailAlreadyVerifiedException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\User as DomainUser;
use App\Infrastructure\Notifications\Auth\VerifyEmailForRegisterNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResendEmailActionTest extends TestCase
{
    use RefreshDatabase;

    private ResendVerificationEmail $action;

    private CreateNewUser $createUserAction;

    private User $user;

    private DomainUser $domainUser;

    private string $email = 'test@example.com';

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(ResendVerificationEmail::class);
        $this->createUserAction = app(CreateNewUser::class);

        Notification::fake();

        $requestData = new CreatingUserRequestData(
            firstName: 'Иван',
            lastName: 'Петров',
            email: $this->email,
            uniqueNickname: 'test_user',
            password: 'Password123!',
            middleName: null,
        );

        $this->domainUser = $this->createUserAction->run($requestData);
        $this->user = User::query()->findOrFail($this->domainUser->id);
    }

    public function test_it_sends_verification_email_successfully(): void
    {
        $data = ResendEmailRequestData::fromArray(['email' => $this->email]);
        $this->action->run($data);

        Notification::assertSentOnDemand(VerifyEmailForRegisterNotification::class);
    }

    public function test_it_does_nothing_when_email_not_found(): void
    {
        Notification::fake();

        $data = ResendEmailRequestData::fromArray(['email' => 'nonexistent@example.com']);
        $this->action->run($data);

        Notification::assertNothingSent();
    }

    public function test_it_throws_exception_when_email_already_verified(): void
    {
        app(UserRepositoryInterface::class)->markEmailVerified($this->domainUser);
        $this->expectException(EmailAlreadyVerifiedException::class);

        $data = ResendEmailRequestData::fromArray(['email' => $this->email]);
        $this->action->run($data);
    }

    public function test_it_does_not_send_verification_to_verified_email(): void
    {
        app(UserRepositoryInterface::class)->markEmailVerified($this->domainUser);
        Notification::fake();

        try {
            $data = ResendEmailRequestData::fromArray(['email' => $this->email]);
            $this->action->run($data);
        } catch (EmailAlreadyVerifiedException $e) {
        }

        Notification::assertNothingSent();
    }

    public function test_it_can_resend_multiple_times_if_not_verified(): void
    {
        $data = ResendEmailRequestData::fromArray(['email' => $this->email]);

        $this->action->run($data);
        $this->action->run($data);
        $this->action->run($data);

        Notification::assertSentOnDemandTimes(VerifyEmailForRegisterNotification::class, 3);
    }
}
