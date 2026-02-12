<?php

namespace Tests\Unit\User;

use App\Actions\User\CreateNewUserAction;
use App\Actions\User\Email\VerifyEmailAction;
use App\DTO\User\CreatingUserDTO;
use App\Exceptions\User\Email\EmailAlreadyVerifiedException;
use App\Exceptions\User\Email\InvalidConfirmationLinkException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class VerifyEmailActionTest extends TestCase
{
    use RefreshDatabase;

    private VerifyEmailAction $action;
    private CreateNewUserAction $createUserAction;
    private User $user;
    private string $email = 'test@example.com';
    private string $hash;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(VerifyEmailAction::class);
        $this->createUserAction = app(CreateNewUserAction::class);

        $dto = new CreatingUserDTO(
            firstName: 'Иван',
            lastName: 'Петров',
            email: $this->email,
            uniqueNickname: 'verify_test',
            password: 'Password123!',
            middleName: null
        );

        $this->user = $this->createUserAction->run($dto);
        $this->hash = sha1($this->user->getEmailForVerification());
    }

    public function test_it_verifies_email_successfully(): void
    {
        $token = $this->action->run($this->user->id, $this->hash);

        $this->user->refresh();

        $this->assertNotNull($this->user->email_verified_at);
        $this->assertInstanceOf(Carbon::class, $this->user->email_verified_at);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('|', $token);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
            'name' => 'auth_token'
        ]);
    }

    public function test_it_throws_exception_when_user_not_found(): void
    {
        $nonExistentId = 99999;
        $this->expectException(UserNotFoundException::class);
        $this->action->run($nonExistentId, $this->hash);
    }

    public function test_it_throws_exception_when_hash_is_invalid(): void
    {
        $invalidHash = 'invalid_hash_123';

        $this->expectException(InvalidConfirmationLinkException::class);

        $this->action->run($this->user->id, $invalidHash);
    }

    public function test_it_throws_exception_when_email_already_verified(): void
    {
        $this->action->run($this->user->id, $this->hash);

        $this->expectException(EmailAlreadyVerifiedException::class);

        $this->action->run($this->user->id, $this->hash);
    }

    public function test_it_does_not_create_token_when_email_already_verified(): void
    {
        $this->action->run($this->user->id, $this->hash);

        $tokensBefore = $this->user->tokens()->count();

        try {
            $this->action->run($this->user->id, $this->hash);
        } catch (EmailAlreadyVerifiedException $e) {
        }

        $this->assertEquals($tokensBefore, $this->user->tokens()->count());
    }

    public function test_it_creates_token_only_on_first_verification(): void
    {
        $this->assertEquals(0, $this->user->tokens()->count());

        $this->action->run($this->user->id, $this->hash);
        $this->assertEquals(1, $this->user->tokens()->count());

        $this->user->refresh();

        try {
            $this->action->run($this->user->id, $this->hash);
        } catch (EmailAlreadyVerifiedException $e) {
        }

        $this->assertEquals(1, $this->user->tokens()->count());
    }
}
