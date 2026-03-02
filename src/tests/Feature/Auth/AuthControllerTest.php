<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Domain\User\Auth\Exceptions\IncorrectLoginDataException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ApiAssertions;
use Tests\Concerns\CreatesVerifiedUser;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use ApiAssertions;
    use CreatesVerifiedUser;
    use RefreshDatabase;

    private const string REGISTER_API = '/api/v1/register';
    private const string LOGIN_API = '/api/v1/login';
    private const string LOGOUT_API = '/api/v1/logout';
    private const string LOGOUT_ALL_API = '/api/v1/logout-all';

    /** @return array<string, mixed> */
    private function registerPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'email' => 'test@example.com',
            'unique_nickname' => 'ivan_petrov',
            'password' => $this->defaultPassword(),
            'middle_name' => 'Иванович',
        ], $overrides);
    }

    public function test_it_registers_user_successfully(): void
    {
        $payload = $this->registerPayload();

        $response = $this->postJson(self::REGISTER_API, $payload);

        $response->assertStatus(201)
            ->assertJsonStructure(['success', 'message', 'data' => ['user_id', 'email']])
            ->assertJson([
                'success' => true,
                'message' => __('messages.email-verify'),
            ]);
        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'middle_name' => $payload['middle_name'],
            'unique_nickname' => $payload['unique_nickname'],
        ]);
    }

    public function test_it_registers_user_without_middle_name(): void
    {
        $payload = $this->registerPayload([
            'first_name' => 'Петр',
            'last_name' => 'Иванов',
            'email' => 'petr@example.com',
            'unique_nickname' => 'petr_ivanov',
        ]);
        unset($payload['middle_name']);

        $response = $this->postJson(self::REGISTER_API, $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'petr@example.com',
            'middle_name' => null,
        ]);
    }

    public function test_it_rate_limits_registration_requests(): void
    {
        $payload = $this->registerPayload([
            'email' => 'blocked@example.com',
            'password_confirmation' => $this->defaultPassword(),
        ]);

        for ($i = 0; $i < 3; $i++) {
            $payload['unique_nickname'] = "user_{$i}";
            $this->postJson(self::REGISTER_API, $payload)->assertStatus(201);
        }

        $payload['unique_nickname'] = 'blocked_user';
        $this->postJson(self::REGISTER_API, $payload)->assertStatus(429);
    }

    public function test_it_validates_required_fields_on_register(): void
    {
        $this->assertApiValidationErrors(
            $this->postJson(self::REGISTER_API, []),
            ['first_name', 'last_name', 'email', 'password', 'unique_nickname']
        );
    }

    public function test_it_validates_email_format(): void
    {
        $this->assertApiValidationErrors(
            $this->postJson(self::REGISTER_API, $this->registerPayload(['email' => 'not-an-email'])),
            ['email']
        );
    }

    public function test_it_validates_unique_nickname_format(): void
    {
        $this->assertApiValidationErrors(
            $this->postJson(self::REGISTER_API, $this->registerPayload(['unique_nickname' => 'никнейм-с-кириллицей'])),
            ['unique_nickname']
        );
    }

    public function test_it_logins_user_successfully(): void
    {
        $user = $this->createVerifiedUser([
            'email' => 'login@example.com',
            'unique_nickname' => 'login_user',
        ]);

        $response = $this->postJson(self::LOGIN_API, [
            'email' => $user->email,
            'password' => $this->defaultPassword(),
        ]);

        $this->assertApiSuccess($response, 200, __('auth.login'));
        $response->assertJsonStructure(['data' => ['token']]);
        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_it_returns_401_without_email_verification(): void
    {
        $user = $this->createUnverifiedUser([
            'email' => 'unverified@example.com',
            'unique_nickname' => 'unverified_user',
        ]);

        $response = $this->postJson(self::LOGIN_API, [
            'email' => $user->email,
            'password' => $this->defaultPassword(),
        ]);

        $this->assertApiError($response, 401, __('exceptions.'.IncorrectLoginDataException::class));
    }

    public function test_it_returns_401_on_invalid_credentials(): void
    {
        $response = $this->postJson(self::LOGIN_API, [
            'email' => 'wrong@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertApiError($response, 401, __('exceptions.'.IncorrectLoginDataException::class));
    }

    public function test_it_validates_login_fields(): void
    {
        $this->assertApiValidationErrors(
            $this->postJson(self::LOGIN_API, []),
            ['email', 'password']
        );
    }

    public function test_it_logouts_user_successfully(): void
    {
        $user = $this->createVerifiedUser();
        $token = $this->postJson(self::LOGIN_API, [
            'email' => $user->email,
            'password' => $this->defaultPassword(),
        ])->json('data.token');

        $response = $this->withToken($token)->postJson(self::LOGOUT_API);

        $this->assertApiSuccess($response, 200, __('auth.logout'));
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
    }

    public function test_it_logouts_from_all_devices(): void
    {
        $user = $this->createVerifiedUser();
        $token1 = $user->createToken('device_1')->plainTextToken;
        $user->createToken('device_2')->plainTextToken;
        $this->assertSame(2, $user->tokens()->count());

        $response = $this->withToken($token1)->postJson(self::LOGOUT_ALL_API);

        $this->assertApiSuccess($response, 200, __('auth.logout-all'));
        $user->refresh();
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_it_requires_authentication_for_logout(): void
    {
        $this->postJson(self::LOGOUT_API, [])->assertStatus(401);
    }

    public function test_login_response_does_not_include_user_object(): void
    {
        $user = $this->createVerifiedUser();

        $response = $this->postJson(self::LOGIN_API, [
            'email' => $user->email,
            'password' => $this->defaultPassword(),
        ]);

        $response->assertJsonMissingPath('data.user');
    }
}
