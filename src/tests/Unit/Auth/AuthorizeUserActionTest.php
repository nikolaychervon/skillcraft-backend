<?php

namespace Tests\Unit\Auth;

use App\Application\Auth\Assemblers\LoginUserDTOAssembler;
use App\Domain\Auth\Actions\LoginUserAction;
use App\Domain\Auth\Actions\CreateNewUserAction;
use App\Domain\Auth\DTO\CreatingUserDTO;
use App\Domain\Auth\Exceptions\IncorrectLoginDataException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizeUserActionTest extends TestCase
{
    use RefreshDatabase;

    private LoginUserAction $action;
    private CreateNewUserAction $createUserAction;
    private LoginUserDTOAssembler $loginUserDtoAssembler;
    private User $user;
    private string $password = 'Password123!';

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(LoginUserAction::class);
        $this->createUserAction = app(CreateNewUserAction::class);

        $dto = new CreatingUserDTO(
            firstName: 'Иван',
            lastName: 'Петров',
            email: 'ivan@example.com',
            uniqueNickname: 'ivan_petrov',
            password: $this->password,
            middleName: null
        );

        $this->user = $this->createUserAction->run($dto);
        $this->loginUserDtoAssembler = app(LoginUserDTOAssembler::class);
    }

    public function test_it_returns_token_on_successful_login(): void
    {
        $this->user->markEmailAsVerified();
        $dto = $this->loginUserDtoAssembler->assemble([
            'email' => 'ivan@example.com',
            'password' => $this->password,
        ]);

        $token = $this->action->run($dto);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
            'name' => 'auth_token'
        ]);
    }

    public function test_it_throws_exception_when_email_not_found(): void
    {
        $this->expectException(IncorrectLoginDataException::class);

        $dto = $this->loginUserDtoAssembler->assemble([
            'email' => 'nonexistent@example.com',
            'password' => $this->password,
        ]);

        $this->action->run($dto);
    }

    public function test_it_throws_exception_when_password_is_incorrect(): void
    {
        $this->expectException(IncorrectLoginDataException::class);

        $dto = $this->loginUserDtoAssembler->assemble([
            'email' => 'ivan@example.com',
            'password' => 'WrongPassword123!',
        ]);

        $this->action->run($dto);
    }

    public function test_it_throws_exception_when_email_is_empty(): void
    {
        $this->expectException(IncorrectLoginDataException::class);

        $dto = $this->loginUserDtoAssembler->assemble([
            'email' => '',
            'password' => $this->password,
        ]);

        $this->action->run($dto);
    }

    public function test_it_throws_exception_when_password_is_empty(): void
    {
        $this->expectException(IncorrectLoginDataException::class);
        $dto = $this->loginUserDtoAssembler->assemble([
            'email' => 'ivan@example.com',
            'password' => '',
        ]);

        $this->action->run($dto);
    }

    public function test_it_throws_exception_with_non_existent_user(): void
    {
        $this->expectException(IncorrectLoginDataException::class);
        $dto = $this->loginUserDtoAssembler->assemble([
            'email' => 'deleted@example.com',
            'password' => $this->password,
        ]);

        $this->action->run($dto);
    }
}
