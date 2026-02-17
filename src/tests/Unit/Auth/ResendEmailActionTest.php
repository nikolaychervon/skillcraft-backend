<?php

namespace Tests\Unit\Auth;

use App\Application\Auth\Assemblers\ResendEmailDTOAssembler;
use App\Application\Shared\Exceptions\User\Email\EmailAlreadyVerifiedException;
use App\Domain\Auth\Actions\CreateNewUserAction;
use App\Domain\Auth\Actions\Email\ResendEmailAction;
use App\Domain\Auth\DTO\CreatingUserDTO;
use App\Infrastructure\Notifications\Auth\VerifyEmailForRegisterNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResendEmailActionTest extends TestCase
{
    use RefreshDatabase;

    private ResendEmailAction $action;
    private CreateNewUserAction $createUserAction;
    private ResendEmailDTOAssembler $resendEmailDTOAssembler;
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
        $this->resendEmailDTOAssembler = app(ResendEmailDTOAssembler::class);
    }

    public function test_it_sends_verification_email_successfully(): void
    {
        $this->action->run($this->resendEmailDTOAssembler->assemble([
            'email' => $this->email,
        ]));

        Notification::assertSentTo(
            $this->user,
            VerifyEmailForRegisterNotification::class
        );
    }

    public function test_it_does_nothing_when_email_not_found(): void
    {
        Notification::fake();

        $this->action->run($this->resendEmailDTOAssembler->assemble([
            'email' => 'nonexistent@example.com',
        ]));

        Notification::assertNothingSent();
    }

    public function test_it_throws_exception_when_email_already_verified(): void
    {
        $this->user->markEmailAsVerified();

        $this->expectException(EmailAlreadyVerifiedException::class);

        $this->action->run($this->resendEmailDTOAssembler->assemble([
            'email' => $this->email,
        ]));
    }

    public function test_it_does_not_send_verification_to_verified_email(): void
    {
        $this->user->markEmailAsVerified();

        Notification::fake();

        try {
            $this->action->run($this->resendEmailDTOAssembler->assemble([
                'email' => $this->email,
            ]));
        } catch (EmailAlreadyVerifiedException $e) {
        }

        Notification::assertNothingSent();
    }

    public function test_it_can_resend_multiple_times_if_not_verified(): void
    {
        $dto = $this->resendEmailDTOAssembler->assemble([
            'email' => $this->email,
        ]);

        $this->action->run($dto);
        $this->action->run($dto);
        $this->action->run($dto);

        Notification::assertSentToTimes(
            $this->user,
            VerifyEmailForRegisterNotification::class,
            3
        );
    }
}
