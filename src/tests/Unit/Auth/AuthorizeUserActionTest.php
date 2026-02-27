<?php

declare(strict_types=1);

namespace Tests\Unit\Auth;

use App\Application\User\Auth\CreateNewUser;
use App\Application\User\Auth\LoginUser;
use App\Domain\User\Auth\Exceptions\IncorrectLoginDataException;
use App\Domain\User\Auth\RequestData\CreatingUserRequestData;
use App\Domain\User\Auth\RequestData\LoginUserRequestData;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizeUserActionTest extends TestCase
{
    use RefreshDatabase;

    private LoginUser $action;

    private CreateNewUser $createUserAction;

    private User $user;

    private string $password = 'Password123!';

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(LoginUser::class);
        $this->createUserAction = app(CreateNewUser::class);

        $requestData = new CreatingUserRequestData(
            firstName: 'Иван',
            lastName: 'Петров',
            email: 'ivan@example.com',
            uniqueNickname: 'ivan_petrov',
            password: $this->password,
            middleName: null,
        );

        $this->user = $this->createUserAction->run($requestData);
    }

    public function test_it_returns_token_on_successful_login(): void
    {
        app(UserRepositoryInterface::class)->markEmailVerified($this->user);
        $data = LoginUserRequestData::fromArray([
            'email' => 'ivan@example.com',
            'password' => $this->password,
        ]);

        $token = $this->action->run($data);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
            'name' => 'auth_token',
        ]);
    }

    public function test_it_throws_exception_when_email_not_found(): void
    {
        $this->expectException(IncorrectLoginDataException::class);

        $data = LoginUserRequestData::fromArray([
            'email' => 'nonexistent@example.com',
            'password' => $this->password,
        ]);
        $this->action->run($data);
    }

    public function test_it_throws_exception_when_password_is_incorrect(): void
    {
        app(UserRepositoryInterface::class)->markEmailVerified($this->user);
        $this->expectException(IncorrectLoginDataException::class);

        $data = LoginUserRequestData::fromArray([
            'email' => 'ivan@example.com',
            'password' => 'WrongPassword123!',
        ]);
        $this->action->run($data);
    }

    public function test_it_throws_exception_when_email_not_verified(): void
    {
        $this->expectException(IncorrectLoginDataException::class);

        $data = LoginUserRequestData::fromArray([
            'email' => 'ivan@example.com',
            'password' => $this->password,
        ]);
        $this->action->run($data);
    }

    public function test_it_throws_exception_when_email_is_empty(): void
    {
        $this->expectException(IncorrectLoginDataException::class);

        $data = LoginUserRequestData::fromArray([
            'email' => '',
            'password' => $this->password,
        ]);
        $this->action->run($data);
    }

    public function test_it_throws_exception_when_password_is_empty(): void
    {
        $this->expectException(IncorrectLoginDataException::class);

        $data = LoginUserRequestData::fromArray([
            'email' => 'ivan@example.com',
            'password' => '',
        ]);
        $this->action->run($data);
    }

    public function test_it_throws_exception_with_non_existent_user(): void
    {
        $this->expectException(IncorrectLoginDataException::class);

        $data = LoginUserRequestData::fromArray([
            'email' => 'deleted@example.com',
            'password' => $this->password,
        ]);
        $this->action->run($data);
    }
}
