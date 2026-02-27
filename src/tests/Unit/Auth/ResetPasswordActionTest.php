<?php

namespace Tests\Unit\Auth;

use App\Application\User\Auth\CreateNewUser;
use App\Application\User\Auth\ResetPassword;
use App\Application\User\Auth\SendPasswordResetLink;
use App\Domain\User\Auth\Cache\PasswordResetTokensCacheInterface;
use App\Domain\User\Auth\Exceptions\InvalidResetTokenException;
use App\Domain\User\Auth\Exceptions\PasswordResetFailedException;
use App\Domain\User\Auth\RequestData\CreatingUserRequestData;
use App\Domain\User\Auth\RequestData\ResetPasswordRequestData;
use App\Domain\User\Auth\Services\HashServiceInterface;
use App\Domain\User\Auth\Services\TokenServiceInterface;
use App\Domain\User\Auth\Services\TransactionServiceInterface;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\User as DomainUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetPasswordActionTest extends TestCase
{
    use RefreshDatabase;

    private ResetPassword $action;

    private SendPasswordResetLink $sendResetLinkAction;

    private PasswordResetTokensCacheInterface $cache;

    private User $user;

    private DomainUser $domainUser;

    private string $email = 'test@example.com';

    private string $newPassword = 'NewPassword123!';

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(ResetPassword::class);
        $this->sendResetLinkAction = app(SendPasswordResetLink::class);
        $this->cache = app(PasswordResetTokensCacheInterface::class);

        $createUserAction = app(CreateNewUser::class);
        $requestData = new CreatingUserRequestData(
            firstName: 'Иван',
            lastName: 'Петров',
            email: $this->email,
            uniqueNickname: 'reset_test',
            password: 'OldPassword123!',
            middleName: null
        );

        $this->domainUser = $createUserAction->run($requestData);
        $this->user = User::query()->findOrFail($this->domainUser->id);
        $this->user->markEmailAsVerified();
    }

    public function test_it_resets_password_successfully(): void
    {
        $this->sendResetLinkAction->run($this->email);
        $token = $this->cache->get($this->email);

        $requestData = new ResetPasswordRequestData(
            email: $this->email,
            resetToken: $token,
            password: $this->newPassword
        );

        $authToken = $this->action->run($requestData);

        $this->user->refresh();
        $this->assertNotEquals('OldPassword123!', $this->user->password);
        $this->assertTrue(Hash::check($this->newPassword, $this->user->password));

        $this->assertNull($this->cache->get($this->email));

        $this->assertIsString($authToken);
        $this->assertNotEmpty($authToken);
        $this->assertStringContainsString('|', $authToken);
    }

    public function test_it_deletes_all_sanctum_tokens_after_password_reset(): void
    {
        $this->sendResetLinkAction->run($this->email);
        $token = $this->cache->get($this->email);

        $this->user->createToken('device_1');
        $this->user->createToken('device_2');
        $this->assertEquals(2, $this->user->tokens()->count());

        $requestData = new ResetPasswordRequestData(
            email: $this->email,
            resetToken: $token,
            password: $this->newPassword
        );

        $this->action->run($requestData);

        $this->assertEquals(1, $this->user->tokens()->count());
    }

    public function test_it_throws_exception_when_token_is_invalid(): void
    {
        $requestData = new ResetPasswordRequestData(
            email: $this->email,
            resetToken: 'invalid_token_123',
            password: $this->newPassword
        );

        $this->expectException(InvalidResetTokenException::class);
        $this->action->run($requestData);
    }

    public function test_it_throws_exception_when_token_expired(): void
    {
        $this->sendResetLinkAction->run($this->email);
        $token = $this->cache->get($this->email);

        $this->cache->delete($this->email);

        $requestData = new ResetPasswordRequestData(
            email: $this->email,
            resetToken: $token,
            password: $this->newPassword
        );

        $this->expectException(InvalidResetTokenException::class);
        $this->action->run($requestData);
    }

    public function test_it_throws_exception_when_user_not_found(): void
    {
        $nonExistentEmail = 'nonexistent@example.com';
        $this->sendResetLinkAction->run($nonExistentEmail);

        $this->cache->store($nonExistentEmail, 'some_token');
        $token = $this->cache->get($nonExistentEmail);

        $requestData = new ResetPasswordRequestData(
            email: $nonExistentEmail,
            resetToken: $token,
            password: $this->newPassword
        );

        $this->expectException(UserNotFoundException::class);
        $this->action->run($requestData);
    }

    public function test_it_throws_exception_when_password_reset_fails(): void
    {
        $this->sendResetLinkAction->run($this->email);
        $token = $this->cache->get($this->email);

        $requestData = new ResetPasswordRequestData(
            email: $this->email,
            resetToken: $token,
            password: $this->newPassword
        );

        $mock = $this->createMock(UserRepositoryInterface::class);
        $mock->method('findByEmail')
            ->willThrowException(new PasswordResetFailedException);

        $this->app->instance(UserRepositoryInterface::class, $mock);

        $action = $this->app->make(ResetPassword::class);

        $this->expectException(PasswordResetFailedException::class);
        $action->run($requestData);
    }

    public function test_it_wraps_exception_when_update_password_fails_inside_transaction(): void
    {
        $this->sendResetLinkAction->run($this->email);
        $token = $this->cache->get($this->email);

        $requestData = new ResetPasswordRequestData(
            email: $this->email,
            resetToken: $token,
            password: $this->newPassword
        );

        $repo = $this->createMock(UserRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn($this->domainUser);
        $repo->method('updatePassword')->willThrowException(new \RuntimeException('db write failed'));

        $hashService = app(HashServiceInterface::class);

        $tokenService = $this->createMock(TokenServiceInterface::class);
        $transactionService = $this->createMock(TransactionServiceInterface::class);
        $transactionService->expects($this->once())
            ->method('transaction')
            ->willReturnCallback(static fn (callable $callback) => $callback());

        $action = new ResetPassword(
            userRepository: $repo,
            passwordResetTokensCache: $this->cache,
            hashService: $hashService,
            tokenService: $tokenService,
            transactionService: $transactionService
        );

        try {
            $action->run($requestData);
            $this->fail('Expected PasswordResetFailedException was not thrown.');
        } catch (PasswordResetFailedException $e) {
            $this->assertInstanceOf(\RuntimeException::class, $e->getPrevious());
            $this->assertSame('db write failed', $e->getPrevious()->getMessage());
        }
    }

    public function test_it_uses_transaction(): void
    {
        $this->sendResetLinkAction->run($this->email);
        $token = $this->cache->get($this->email);

        $requestData = new ResetPasswordRequestData(
            email: $this->email,
            resetToken: $token,
            password: $this->newPassword
        );

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(static fn (callable $callback) => $callback());

        $this->action->run($requestData);
    }
}
