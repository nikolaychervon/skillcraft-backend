<?php

namespace Tests\Unit\Auth;

use App\Domain\Auth\Actions\CreateNewUserAction;
use App\Domain\Auth\DTO\CreatingUserDTO;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateNewUserActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateNewUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(CreateNewUserAction::class);
    }

    public function test_it_creates_user_successfully(): void
    {
        $dto = new CreatingUserDTO(
            firstName: 'Иван',
            lastName: 'Петров',
            email: 'ivan@example.com',
            uniqueNickname: 'ivan_petrov',
            password: 'Password123!',
            middleName: 'Иванович'
        );

        $user = $this->action->run($dto);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'email' => 'ivan@example.com',
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'middle_name' => 'Иванович',
            'unique_nickname' => 'ivan_petrov',
        ]);

        $this->assertNotEquals('Password123!', $user->password);
        $this->assertTrue(Hash::check('Password123!', $user->password));
    }

    public function test_it_creates_user_without_middle_name(): void
    {
        $dto = new CreatingUserDTO(
            firstName: 'Петр',
            lastName: 'Иванов',
            email: 'petr@example.com',
            uniqueNickname: 'petr_ivanov',
            password: 'Password123!',
            middleName: null
        );

        $user = $this->action->run($dto);

        $this->assertDatabaseHas('users', [
            'email' => 'petr@example.com',
            'first_name' => 'Петр',
            'last_name' => 'Иванов',
            'middle_name' => null,
            'unique_nickname' => 'petr_ivanov',
        ]);

        $this->assertNull($user->middle_name);
    }

    public function test_it_hashes_password(): void
    {
        $dto = new CreatingUserDTO(
            firstName: 'Иван',
            lastName: 'Петров',
            email: 'hash@example.com',
            uniqueNickname: 'hash_test',
            password: 'PlainTextPassword123!',
            middleName: null
        );

        $user = $this->action->run($dto);

        $this->assertNotEquals('PlainTextPassword123!', $user->password);
        $this->assertStringStartsWith('$2y$', $user->password);
        $this->assertTrue(Hash::check('PlainTextPassword123!', $user->password));
    }
}
