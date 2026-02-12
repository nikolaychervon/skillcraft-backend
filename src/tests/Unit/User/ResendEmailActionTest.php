<?php

namespace Tests\Unit\User;

use App\Actions\User\CreateNewUserAction;
use App\Actions\User\Email\ResendEmailAction;
use App\DTO\User\CreatingUserDTO;
use App\Exceptions\User\Email\EmailAlreadyVerifiedException;
use App\Models\User;
use App\Notifications\User\VerifyEmailForRegisterNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResendEmailActionTest extends TestCase
{
    use RefreshDatabase;

    private ResendEmailAction $action;
    private CreateNewUserAction $createUserAction;
    private User $user;
    private string $email = 'test@example.com';

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(ResendEmailAction::class);
        $this->createUserAction = app(CreateNewUserAction::class);

        Notification::fake();

        $dto = new CreatingUserDTO(
            firstName: 'Иван',
            lastName: 'Петров',
            email: $this->email,
            uniqueNickname: 'test_user',
            password: 'Password123!',
            middleName: null
        );

        $this->user = $this->createUserAction->run($dto);
    }

    public function test_it_sends_verification_email_successfully(): void
    {
        $this->action->run($this->email);

        Notification::assertSentTo(
            $this->user,
            VerifyEmailForRegisterNotification::class
        );
    }

    public function test_it_does_nothing_when_email_not_found(): void
    {
        Notification::fake();

        $this->action->run('nonexistent@example.com');

        Notification::assertNothingSent();
    }

    public function test_it_throws_exception_when_email_already_verified(): void
    {
        $this->user->markEmailAsVerified();

        $this->expectException(EmailAlreadyVerifiedException::class);

        $this->action->run($this->email);
    }

    public function test_it_does_not_send_verification_to_verified_email(): void
    {
        $this->user->markEmailAsVerified();

        Notification::fake();

        try {
            $this->action->run($this->email);
        } catch (EmailAlreadyVerifiedException $e) {
        }

        Notification::assertNothingSent();
    }

    public function test_it_can_resend_multiple_times_if_not_verified(): void
    {
        $this->action->run($this->email);
        $this->action->run($this->email);
        $this->action->run($this->email);

        Notification::assertSentToTimes(
            $this->user,
            VerifyEmailForRegisterNotification::class,
            3
        );
    }
}
