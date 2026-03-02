<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Domain\User\Auth\Cache\PasswordResetTokensCacheInterface;
use App\Domain\User\Auth\Exceptions\InvalidResetTokenException;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Infrastructure\Notifications\Auth\PasswordResetNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\ApiAssertions;
use Tests\Concerns\CreatesVerifiedUser;
use Tests\TestCase;

class PasswordResetControllerTest extends TestCase
{
    use ApiAssertions;
    use CreatesVerifiedUser;
    use RefreshDatabase;

    private const string FORGOT_PASSWORD_API = '/api/v1/forgot-password';
    private const string RESET_PASSWORD_API = '/api/v1/reset-password';
    private const string NEW_PASSWORD = 'NewPassword123!';

    private User $user;
    private PasswordResetTokensCacheInterface $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = app(PasswordResetTokensCacheInterface::class);
        $this->user = $this->createVerifiedUser([
            'email' => 'reset@example.com',
            'unique_nickname' => 'password_reset_user',
        ]);
    }

    /** @return array<string, string> */
    private function resetPayload(string $email, string $token, string $password = self::NEW_PASSWORD): array
    {
        return [
            'email' => $email,
            'reset_token' => $token,
            'password' => $password,
            'password_confirmation' => $password,
        ];
    }

    public function test_it_sends_password_reset_link_successfully(): void
    {
        Notification::fake();

        $response = $this->postJson(self::FORGOT_PASSWORD_API, ['email' => $this->user->email]);

        $this->assertApiSuccess($response, 200, __('messages.password-reset-link'));
        Notification::assertSentOnDemand(PasswordResetNotification::class);
        $this->assertNotNull($this->cache->get($this->user->email));
    }

    public function test_it_returns_success_even_if_email_not_found_on_forgot(): void
    {
        Notification::fake();

        $response = $this->postJson(self::FORGOT_PASSWORD_API, ['email' => 'nonexistent@example.com']);

        $this->assertApiSuccess($response, 200, __('messages.password-reset-link'));
        Notification::assertNothingSent();
        $this->assertNull($this->cache->get('nonexistent@example.com'));
    }

    public function test_it_validates_email_on_forgot_password(): void
    {
        $this->assertApiValidationErrors(
            $this->postJson(self::FORGOT_PASSWORD_API, ['email' => 'not-an-email']),
            ['email']
        );
    }

    public function test_it_requires_email_on_forgot_password(): void
    {
        $this->assertApiValidationErrors(
            $this->postJson(self::FORGOT_PASSWORD_API, []),
            ['email']
        );
    }

    public function test_it_rate_limits_forgot_password_requests(): void
    {
        $this->postJson(self::FORGOT_PASSWORD_API, ['email' => $this->user->email])->assertStatus(200);
        $this->postJson(self::FORGOT_PASSWORD_API, ['email' => $this->user->email])->assertStatus(429);
    }

    public function test_it_resets_password_successfully(): void
    {
        $this->postJson(self::FORGOT_PASSWORD_API, ['email' => $this->user->email]);
        $token = $this->cache->get($this->user->email);
        $this->assertNotNull($token);

        $response = $this->postJson(self::RESET_PASSWORD_API, $this->resetPayload($this->user->email, $token));

        $this->assertApiSuccess($response, 200, __('messages.password-reset-successful'));
        $response->assertJsonStructure(['data' => ['token']]);
        $this->user->refresh();
        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $this->user->password));
        $this->assertNull($this->cache->get($this->user->email));
        $authToken = $response->json('data.token');
        $this->assertNotEmpty($authToken);
        $this->assertStringContainsString('|', $authToken);
    }

    public function test_it_returns_422_on_invalid_reset_token(): void
    {
        $response = $this->postJson(self::RESET_PASSWORD_API, $this->resetPayload(
            $this->user->email,
            'invalid-token-123'
        ));

        $this->assertApiError($response, 422, __('exceptions.'.InvalidResetTokenException::class));
    }

    public function test_it_returns_422_when_token_expired(): void
    {
        $this->postJson(self::FORGOT_PASSWORD_API, ['email' => $this->user->email]);
        $token = $this->cache->get($this->user->email);
        $this->assertNotNull($token);
        $this->cache->delete($this->user->email);

        $response = $this->postJson(self::RESET_PASSWORD_API, $this->resetPayload($this->user->email, $token));

        $this->assertApiError($response, 422, __('exceptions.'.InvalidResetTokenException::class));
    }

    public function test_it_returns_404_when_user_not_found_on_reset(): void
    {
        $this->cache->store('nonexistent@example.com', 'some-token');
        $token = $this->cache->get('nonexistent@example.com');

        $response = $this->postJson(self::RESET_PASSWORD_API, $this->resetPayload('nonexistent@example.com', $token));

        $this->assertApiError($response, 404, __('exceptions.'.UserNotFoundException::class));
    }

    #[DataProvider('resetValidationProvider')]
    public function test_it_validates_reset_password_request(array $payload, array $expectedErrors): void
    {
        $this->assertApiValidationErrors(
            $this->postJson(self::RESET_PASSWORD_API, $payload),
            $expectedErrors
        );
    }

    /** @return array<string, array{0: array<string, string>, 1: array<int, string>}> */
    public static function resetValidationProvider(): array
    {
        return [
            'missing_all' => [[], ['email', 'reset_token', 'password']],
            'invalid_email' => [
                ['email' => 'not-an-email', 'reset_token' => 't', 'password' => self::NEW_PASSWORD, 'password_confirmation' => self::NEW_PASSWORD],
                ['email'],
            ],
            'password_mismatch' => [
                ['email' => 'e@e.com', 'reset_token' => 't', 'password' => self::NEW_PASSWORD, 'password_confirmation' => 'wrong'],
                ['password'],
            ],
            'weak_password' => [
                ['email' => 'e@e.com', 'reset_token' => 't', 'password' => 'weak', 'password_confirmation' => 'weak'],
                ['password'],
            ],
        ];
    }

    public function test_it_rate_limits_reset_password_attempts(): void
    {
        $payload = $this->resetPayload($this->user->email, 'invalid');
        for ($i = 0; $i < 3; $i++) {
            $this->postJson(self::RESET_PASSWORD_API, $payload)->assertStatus(422);
        }
        $this->postJson(self::RESET_PASSWORD_API, $payload)->assertStatus(429);
    }

    public function test_it_deletes_old_tokens_after_successful_reset(): void
    {
        $this->user->createToken('device_1')->plainTextToken;
        $this->user->createToken('device_2')->plainTextToken;
        $this->assertSame(2, $this->user->tokens()->count());

        $this->postJson(self::FORGOT_PASSWORD_API, ['email' => $this->user->email]);
        $token = $this->cache->get($this->user->email);
        $this->postJson(self::RESET_PASSWORD_API, $this->resetPayload($this->user->email, $token));

        $this->user->refresh();
        $this->assertSame(1, $this->user->tokens()->count());
    }

    public function test_it_returns_valid_token_after_successful_reset(): void
    {
        $this->postJson(self::FORGOT_PASSWORD_API, ['email' => $this->user->email]);
        $token = $this->cache->get($this->user->email);
        $response = $this->postJson(self::RESET_PASSWORD_API, $this->resetPayload($this->user->email, $token));

        $authToken = $response->json('data.token');
        $this->withToken($authToken)->postJson('/api/v1/logout')->assertStatus(200);
    }
}
