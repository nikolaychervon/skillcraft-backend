<?php

namespace Tests\Unit\User;

use App\Actions\User\CreateNewUserAction;
use App\Actions\User\Password\ResetPasswordAction;
use App\Actions\User\Password\SendPasswordResetLinkAction;
use App\Cache\User\Auth\PasswordResetTokensCache;
use App\DTO\User\CreatingUserDTO;
use App\DTO\User\ResetPasswordDTO;
use App\Exceptions\User\Auth\InvalidResetTokenException;
use App\Exceptions\User\Auth\PasswordResetFailedException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\User;
use App\Repositories\User\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetPasswordActionTest extends TestCase
{
    use RefreshDatabase;

    private ResetPasswordAction $action;
    private SendPasswordResetLinkAction $sendResetLinkAction;
    private PasswordResetTokensCache $cache;
    private User $user;
    private string $email = 'test@example.com';
    private string $newPassword = 'NewPassword123!';

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(ResetPasswordAction::class);
        $this->sendResetLinkAction = app(SendPasswordResetLinkAction::class);
        $this->cache = app(PasswordResetTokensCache::class);

        $createUserAction = app(CreateNewUserAction::class);
        $dto = new CreatingUserDTO(
            firstName: 'Иван',
            lastName: 'Петров',
            email: $this->email,
            uniqueNickname: 'reset_test',
            password: 'OldPassword123!',
            middleName: null
        );

        $this->user = $createUserAction->run($dto);
        $this->user->markEmailAsVerified();
    }

    public function test_it_resets_password_successfully(): void
    {
        $this->sendResetLinkAction->run($this->email);
        $token = $this->cache->getTokenByEmail($this->email);

        $dto = new ResetPasswordDTO(
            email: $this->email,
            resetToken: $token,
            password: $this->newPassword
        );

        $authToken = $this->action->run($dto);

        $this->user->refresh();
        $this->assertNotEquals('OldPassword123!', $this->user->password);
        $this->assertTrue(Hash::check($this->newPassword, $this->user->password));

        $this->assertNull($this->cache->getTokenByEmail($this->email));

        $this->assertIsString($authToken);
        $this->assertNotEmpty($authToken);
        $this->assertStringContainsString('|', $authToken);
    }

    public function test_it_deletes_all_sanctum_tokens_after_password_reset(): void
    {
        $this->sendResetLinkAction->run($this->email);
        $token = $this->cache->getTokenByEmail($this->email);

        $this->user->createToken('device_1');
        $this->user->createToken('device_2');
        $this->assertEquals(2, $this->user->tokens()->count());

        $dto = new ResetPasswordDTO(
            email: $this->email,
            resetToken: $token,
            password: $this->newPassword
        );

        $this->action->run($dto);

        $this->assertEquals(1, $this->user->tokens()->count());
    }

    public function test_it_throws_exception_when_token_is_invalid(): void
    {
        $dto = new ResetPasswordDTO(
            email: $this->email,
            resetToken: 'invalid_token_123',
            password: $this->newPassword
        );

        $this->expectException(InvalidResetTokenException::class);
        $this->action->run($dto);
    }

    public function test_it_throws_exception_when_token_expired(): void
    {
        $this->sendResetLinkAction->run($this->email);
        $token = $this->cache->getTokenByEmail($this->email);

        $this->cache->delete($this->email);

        $dto = new ResetPasswordDTO(
            email: $this->email,
            resetToken: $token,
            password: $this->newPassword
        );

        $this->expectException(InvalidResetTokenException::class);
        $this->action->run($dto);
    }

    public function test_it_throws_exception_when_user_not_found(): void
    {
        $nonExistentEmail = 'nonexistent@example.com';
        $this->sendResetLinkAction->run($nonExistentEmail);

        $this->cache->save($nonExistentEmail, 'some_token');
        $token = $this->cache->getTokenByEmail($nonExistentEmail);

        $dto = new ResetPasswordDTO(
            email: $nonExistentEmail,
            resetToken: $token,
            password: $this->newPassword
        );

        $this->expectException(UserNotFoundException::class);
        $this->action->run($dto);
    }

    public function test_it_throws_exception_when_password_reset_fails(): void
    {
        $this->sendResetLinkAction->run($this->email);
        $token = $this->cache->getTokenByEmail($this->email);

        $dto = new ResetPasswordDTO(
            email: $this->email,
            resetToken: $token,
            password: $this->newPassword
        );

        $mock = $this->createMock(UserRepository::class);
        $mock->method('findByEmail')
            ->willThrowException(new PasswordResetFailedException());

        $this->app->instance(UserRepository::class, $mock);

        $action = $this->app->make(ResetPasswordAction::class);

        $this->expectException(PasswordResetFailedException::class);
        $action->run($dto);
    }

    public function test_it_uses_transaction(): void
    {
        $this->sendResetLinkAction->run($this->email);
        $token = $this->cache->getTokenByEmail($this->email);

        $dto = new ResetPasswordDTO(
            email: $this->email,
            resetToken: $token,
            password: $this->newPassword
        );

        DB::shouldReceive('beginTransaction')
            ->once()
            ->andReturnNull();

        DB::shouldReceive('commit')
            ->once()
            ->andReturnNull();

        DB::shouldReceive('rollBack')
            ->never();

        $this->action->run($dto);
    }
}
