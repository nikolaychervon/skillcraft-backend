<?php

namespace Tests\Feature\Controllers;

use App\Actions\User\CreateNewUserAction;
use App\Cache\User\Auth\PasswordResetTokensCache;
use App\DTO\User\CreatingUserDTO;
use App\Exceptions\User\Auth\InvalidResetTokenException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\User;
use App\Notifications\User\PasswordResetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string FORGOT_PASSWORD_API = '/api/v1/forgot-password';
    private const string RESET_PASSWORD_API = '/api/v1/reset-password';

    private User $user;
    private string $email = 'test@example.com';
    private string $password = 'Password123!';
    private string $newPassword = 'NewPassword123!';
    private PasswordResetTokensCache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = app(PasswordResetTokensCache::class);

        $createUserAction = app(CreateNewUserAction::class);
        $dto = new CreatingUserDTO(
            firstName: 'Иван',
            lastName: 'Петров',
            email: $this->email,
            uniqueNickname: 'password_reset_controller',
            password: $this->password,
            middleName: null
        );

        $this->user = $createUserAction->run($dto);
        $this->user->markEmailAsVerified();
    }

    public function test_it_sends_password_reset_link_successfully(): void
    {
        Notification::fake();

        $response = $this->postJson(self::FORGOT_PASSWORD_API, [
            'email' => $this->email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __('messages.password-reset-link'),
            ]);

        Notification::assertSentTo(
            $this->user,
            PasswordResetNotification::class
        );

        $token = $this->cache->getTokenByEmail($this->email);
        $this->assertNotNull($token);
    }

    public function test_it_returns_success_even_if_email_not_found_on_forgot(): void
    {
        Notification::fake();

        $response = $this->postJson(self::FORGOT_PASSWORD_API, [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __('messages.password-reset-link'),
            ]);

        Notification::assertNothingSent();

        $token = $this->cache->getTokenByEmail('nonexistent@example.com');
        $this->assertNull($token);
    }

    public function test_it_validates_email_on_forgot_password(): void
    {
        $response = $this->postJson(self::FORGOT_PASSWORD_API, [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_requires_email_on_forgot_password(): void
    {
        $response = $this->postJson(self::FORGOT_PASSWORD_API, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_rate_limits_forgot_password_requests(): void
    {
        $this->postJson(self::FORGOT_PASSWORD_API, [
            'email' => $this->email,
        ])->assertStatus(200);

        $this->postJson(self::FORGOT_PASSWORD_API, [
            'email' => $this->email,
        ])->assertStatus(429);
    }

    public function test_it_resets_password_successfully(): void
    {
        $this->postJson(self::FORGOT_PASSWORD_API, [
            'email' => $this->email,
        ]);

        $token = $this->cache->getTokenByEmail($this->email);
        $this->assertNotNull($token);

        $response = $this->postJson(self::RESET_PASSWORD_API, [
            'email' => $this->email,
            'reset_token' => $token,
            'password' => $this->newPassword,
            'password_confirmation' => $this->newPassword,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token']
            ])
            ->assertJson([
                'success' => true,
                'message' => __('messages.password-reset-successful'),
            ]);

        $this->user->refresh();
        $this->assertTrue(Hash::check($this->newPassword, $this->user->password));

        $this->assertNull($this->cache->getTokenByEmail($this->email));

        $authToken = $response->json('data.token');
        $this->assertNotEmpty($authToken);
        $this->assertStringContainsString('|', $authToken);
    }

    public function test_it_returns_error_on_invalid_reset_token(): void
    {
        $response = $this->postJson(self::RESET_PASSWORD_API, [
            'email' => $this->email,
            'reset_token' => 'invalid-token-123',
            'password' => $this->newPassword,
            'password_confirmation' => $this->newPassword,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => __('exceptions.' . InvalidResetTokenException::class),
            ]);
    }

    public function test_it_returns_error_when_token_expired(): void
    {
        $this->postJson(self::FORGOT_PASSWORD_API, [
            'email' => $this->email,
        ]);

        $token = $this->cache->getTokenByEmail($this->email);
        $this->assertNotNull($token);

        $this->cache->delete($this->email);

        $response = $this->postJson(self::RESET_PASSWORD_API, [
            'email' => $this->email,
            'reset_token' => $token,
            'password' => $this->newPassword,
            'password_confirmation' => $this->newPassword,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => __('exceptions.' . InvalidResetTokenException::class),
            ]);
    }

    public function test_it_returns_error_when_user_not_found_on_reset(): void
    {
        $this->cache->save('nonexistent@example.com', 'some-token-123');
        $token = $this->cache->getTokenByEmail('nonexistent@example.com');

        $response = $this->postJson(self::RESET_PASSWORD_API, [
            'email' => 'nonexistent@example.com',
            'reset_token' => $token,
            'password' => $this->newPassword,
            'password_confirmation' => $this->newPassword,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => __('exceptions.' . UserNotFoundException::class),
            ]);
    }

    public function test_it_validates_reset_password_fields(): void
    {
        $response = $this->postJson(self::RESET_PASSWORD_API, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'reset_token', 'password']);
    }

    public function test_it_validates_email_format_on_reset(): void
    {
        $response = $this->postJson(self::RESET_PASSWORD_API, [
            'email' => 'not-an-email',
            'reset_token' => 'some-token',
            'password' => $this->newPassword,
            'password_confirmation' => $this->newPassword,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_validates_password_confirmation_on_reset(): void
    {
        $response = $this->postJson(self::RESET_PASSWORD_API, [
            'email' => $this->email,
            'reset_token' => 'some-token',
            'password' => $this->newPassword,
            'password_confirmation' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_it_validates_password_complexity_on_reset(): void
    {
        $response = $this->postJson(self::RESET_PASSWORD_API, [
            'email' => $this->email,
            'reset_token' => 'some-token',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_it_rate_limits_reset_password_attempts(): void
    {
        $this->postJson(self::RESET_PASSWORD_API, [
            'email' => $this->email,
            'reset_token' => 'invalid-token',
            'password' => $this->newPassword,
            'password_confirmation' => $this->newPassword,
        ])->assertStatus(422);

        $this->postJson(self::RESET_PASSWORD_API, [
            'email' => $this->email,
            'reset_token' => 'invalid-token-2',
            'password' => $this->newPassword,
            'password_confirmation' => $this->newPassword,
        ])->assertStatus(422);

        $this->postJson(self::RESET_PASSWORD_API, [
            'email' => $this->email,
            'reset_token' => 'invalid-token-3',
            'password' => $this->newPassword,
            'password_confirmation' => $this->newPassword,
        ])->assertStatus(422);

        $this->postJson(self::RESET_PASSWORD_API, [
            'email' => $this->email,
            'reset_token' => 'invalid-token-4',
            'password' => $this->newPassword,
            'password_confirmation' => $this->newPassword,
        ])->assertStatus(429);
    }

    public function test_it_deletes_old_tokens_after_successful_reset(): void
    {
        $this->user->createToken('device_1')->plainTextToken;
        $this->user->createToken('device_2')->plainTextToken;

        $this->assertEquals(2, $this->user->tokens()->count());

        $this->postJson(self::FORGOT_PASSWORD_API, [
            'email' => $this->email,
        ]);

        $token = $this->cache->getTokenByEmail($this->email);

        $this->postJson(self::RESET_PASSWORD_API, [
            'email' => $this->email,
            'reset_token' => $token,
            'password' => $this->newPassword,
            'password_confirmation' => $this->newPassword,
        ]);

        $this->assertEquals(1, $this->user->tokens()->count());
    }

    public function test_it_returns_new_token_after_successful_reset(): void
    {
        $this->postJson(self::FORGOT_PASSWORD_API, [
            'email' => $this->email,
        ]);

        $token = $this->cache->getTokenByEmail($this->email);

        $response = $this->postJson(self::RESET_PASSWORD_API, [
            'email' => $this->email,
            'reset_token' => $token,
            'password' => $this->newPassword,
            'password_confirmation' => $this->newPassword,
        ]);

        $authToken = $response->json('data.token');
        $this->assertNotEmpty($authToken);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $authToken,
        ])->postJson('/api/v1/logout')
            ->assertStatus(200);
    }
}
