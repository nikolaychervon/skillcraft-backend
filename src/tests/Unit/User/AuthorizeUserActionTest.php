<?php

namespace Tests\Unit\User;

use App\Actions\User\Auth\AuthorizeUserAction;
use App\DTO\User\CreatingUserDTO;
use App\Actions\User\CreateNewUserAction;
use App\Exceptions\User\Auth\IncorrectLoginDataException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizeUserActionTest extends TestCase
{
    use RefreshDatabase;

    private AuthorizeUserAction $action;
    private CreateNewUserAction $createUserAction;
    private User $user;
    private string $password = 'Password123!';

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(AuthorizeUserAction::class);
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
    }

    public function test_it_returns_token_on_successful_login(): void
    {
        $token = $this->action->run('ivan@example.com', $this->password);

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
        $this->action->run('nonexistent@example.com', $this->password);
    }

    public function test_it_throws_exception_when_password_is_incorrect(): void
    {
        $this->expectException(IncorrectLoginDataException::class);
        $this->action->run('ivan@example.com', 'WrongPassword123!');
    }

    public function test_it_throws_exception_when_email_is_empty(): void
    {
        $this->expectException(IncorrectLoginDataException::class);
        $this->action->run('', $this->password);
    }

    public function test_it_throws_exception_when_password_is_empty(): void
    {
        $this->expectException(IncorrectLoginDataException::class);
        $this->action->run('ivan@example.com', '');
    }

    public function test_it_throws_exception_with_non_existent_user(): void
    {
        $this->expectException(IncorrectLoginDataException::class);
        $this->action->run('deleted@example.com', $this->password);
    }
}
