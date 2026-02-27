<?php

namespace Tests\Unit\Auth;

use App\Application\User\Auth\CreateNewUser;
use App\Application\User\Auth\VerifyEmail;
use App\Domain\User\Auth\RequestData\CreatingUserRequestData;
use App\Domain\User\Exceptions\Email\EmailAlreadyVerifiedException;
use App\Domain\User\Exceptions\Email\InvalidConfirmationLinkException;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\User\User;
use App\Models\User as UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class VerifyEmailActionTest extends TestCase
{
    use RefreshDatabase;

    private VerifyEmail $action;

    private CreateNewUser $createUserAction;

    private User $domainUser;

    private UserModel $user;

    private string $email = 'test@example.com';

    private string $hash;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(VerifyEmail::class);
        $this->createUserAction = app(CreateNewUser::class);

        $requestData = new CreatingUserRequestData(
            firstName: 'Иван',
            lastName: 'Петров',
            email: $this->email,
            uniqueNickname: 'verify_test',
            password: 'Password123!',
            middleName: null
        );

        $this->domainUser = $this->createUserAction->run($requestData);
        $this->user = UserModel::query()->findOrFail($this->domainUser->id);
        $this->hash = sha1($this->domainUser->email);
    }

    public function test_it_verifies_email_successfully(): void
    {
        $token = $this->action->run($this->domainUser->id, $this->hash);

        $this->user->refresh();

        $this->assertNotNull($this->user->email_verified_at);
        $this->assertInstanceOf(Carbon::class, $this->user->email_verified_at);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('|', $token);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->domainUser->id,
            'name' => 'auth_token',
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

        $this->action->run($this->domainUser->id, $invalidHash);
    }

    public function test_it_throws_exception_when_email_already_verified(): void
    {
        $this->action->run($this->domainUser->id, $this->hash);

        $this->expectException(EmailAlreadyVerifiedException::class);

        $this->action->run($this->domainUser->id, $this->hash);
    }

    public function test_it_does_not_create_token_when_email_already_verified(): void
    {
        $this->action->run($this->domainUser->id, $this->hash);

        $tokensBefore = $this->user->tokens()->count();

        try {
            $this->action->run($this->domainUser->id, $this->hash);
        } catch (EmailAlreadyVerifiedException $e) {
        }

        $this->assertEquals($tokensBefore, $this->user->tokens()->count());
    }

    public function test_it_creates_token_only_on_first_verification(): void
    {
        $this->assertEquals(0, $this->user->tokens()->count());

        $this->action->run($this->domainUser->id, $this->hash);
        $this->assertEquals(1, $this->user->tokens()->count());

        $this->user->refresh();

        try {
            $this->action->run($this->domainUser->id, $this->hash);
        } catch (EmailAlreadyVerifiedException $e) {
        }

        $this->assertEquals(1, $this->user->tokens()->count());
    }
}
