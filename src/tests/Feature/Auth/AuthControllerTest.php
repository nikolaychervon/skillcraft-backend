<?php

namespace Feature\Auth;

use App\Domain\Auth\Actions\CreateNewUserAction;
use App\Domain\Auth\DTO\CreatingUserDTO;
use App\Domain\Auth\Exceptions\IncorrectLoginDataException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string REGISTER_API = '/api/v1/register';
    private const string LOGIN_API = '/api/v1/login';
    private const string LOGOUT_API = '/api/v1/logout';
    private const string LOGOUT_ALL_API = '/api/v1/logout-all';

    private string $email = 'test@example.com';
    private string $password = 'Password123!';

    public function test_it_registers_user_successfully(): void
    {
        $response = $this->postJson(self::REGISTER_API, [
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'email' => $this->email,
            'unique_nickname' => 'ivan_petrov',
            'password' => $this->password,
            'middle_name' => 'Иванович',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user_id', 'email']
            ])
            ->assertJson([
                'success' => true,
                'message' => __('messages.email-verify'),
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $this->email,
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'middle_name' => 'Иванович',
            'unique_nickname' => 'ivan_petrov',
        ]);
    }

    public function test_it_registers_user_without_middle_name(): void
    {
        $response = $this->postJson(self::REGISTER_API, [
            'first_name' => 'Петр',
            'last_name' => 'Иванов',
            'email' => 'petr@example.com',
            'unique_nickname' => 'petr_ivanov',
            'password' => $this->password,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'petr@example.com',
            'first_name' => 'Петр',
            'last_name' => 'Иванов',
            'middle_name' => null,
            'unique_nickname' => 'petr_ivanov',
        ]);
    }

    public function test_it_rate_limits_registration_requests(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $response = $this->postJson('/api/v1/register', [
                'first_name' => 'Иван',
                'last_name' => 'Петров',
                'email' => "blocked@example.com",
                'unique_nickname' => "user{$i}",
                'password' => $this->password,
                'password_confirmation' => $this->password,
            ]);

            if ($i < 3) {
                $response->assertStatus(201);
            } else {
                // Третий запрос — последний разрешённый
                $response->assertStatus(201);
            }
        }

        // Четвёртый запрос — должен быть заблокирован
        $response = $this->postJson('/api/v1/register', [
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'email' => 'blocked@example.com',
            'unique_nickname' => 'blocked_user',
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ]);

        $response->assertStatus(429);
    }

    public function test_it_validates_required_fields_on_register(): void
    {
        $response = $this->postJson(self::REGISTER_API, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'password', 'unique_nickname']);
    }

    public function test_it_validates_email_format(): void
    {
        $response = $this->postJson(self::REGISTER_API, [
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'email' => 'not-an-email',
            'unique_nickname' => 'ivan_petrov',
            'password' => $this->password,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_validates_unique_nickname_format(): void
    {
        $response = $this->postJson(self::REGISTER_API, [
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'email' => $this->email,
            'unique_nickname' => 'никнейм-с-кириллицей',
            'password' => $this->password,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['unique_nickname']);
    }

    public function test_it_logins_user_successfully(): void
    {
        $createUserAction = app(CreateNewUserAction::class);
        $dto = new CreatingUserDTO(
            firstName: 'Иван',
            lastName: 'Петров',
            email: $this->email,
            uniqueNickname: 'verify_controller',
            password: $this->password,
            middleName: null
        );

        $user = $createUserAction->run($dto);
        $user->markEmailAsVerified();

        $response = $this->postJson(self::LOGIN_API, [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token']
            ])
            ->assertJson([
                'success' => true,
                'message' => __('auth.login'),
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_it_logins_user_error_without_email_verification(): void
    {
        $this->postJson(self::REGISTER_API, [
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'email' => $this->email,
            'unique_nickname' => 'ivan_petrov',
            'password' => $this->password,
        ]);

        $response = $this->postJson(self::LOGIN_API, [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => __('exceptions.' . IncorrectLoginDataException::class),
            ]);
    }

    public function test_it_returns_error_on_invalid_login(): void
    {
        $response = $this->postJson(self::LOGIN_API, [
            'email' => 'wrong@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => __('exceptions.' . IncorrectLoginDataException::class),
            ]);
    }

    public function test_it_validates_login_fields(): void
    {
        $response = $this->postJson(self::LOGIN_API, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_it_logouts_user_successfully(): void
    {
        $this->postJson(self::REGISTER_API, [
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'email' => $this->email,
            'unique_nickname' => 'ivan_petrov',
            'password' => $this->password,
        ]);

        $user = User::query()->where('email', $this->email)->first();
        $user->markEmailAsVerified();

        $loginResponse = $this->postJson(self::LOGIN_API, [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        $token = $loginResponse->json('data.token');

        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson(self::LOGOUT_API);

        $logoutResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __('auth.logout'),
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_it_logouts_from_all_devices_successfully(): void
    {
        $this->postJson(self::REGISTER_API, [
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'email' => $this->email,
            'unique_nickname' => 'ivan_petrov',
            'password' => $this->password,
        ]);

        $user = User::query()->where('email', $this->email)->first();
        $user->markEmailAsVerified();

        $token1 = $user->createToken('device_1')->plainTextToken;
        $user->createToken('device_2')->plainTextToken;

        $this->assertEquals(2, $user->tokens()->count());

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
        ])->postJson(self::LOGOUT_ALL_API);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __('auth.logout-all'),
            ]);

        $this->assertEquals(0, $user->tokens()->count());
    }

    public function test_it_requires_authentication_for_logout(): void
    {
        $response = $this->postJson(self::LOGOUT_API, []);
        $response->assertStatus(401);
    }

    public function test_it_returns_user_data_after_login(): void
    {
        $this->postJson(self::REGISTER_API, [
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'email' => $this->email,
            'unique_nickname' => 'ivan_petrov',
            'password' => $this->password,
        ]);

        $user = User::query()->where('email', $this->email)->first();
        $user->markEmailAsVerified();

        $response = $this->postJson(self::LOGIN_API, [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        $response->assertJsonMissingPath('data.user');
    }
}
