<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Domain\User\Exceptions\Email\EmailAlreadyVerifiedException;
use App\Domain\User\Exceptions\Email\InvalidConfirmationLinkException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\Concerns\ApiAssertions;
use Tests\Concerns\BuildsSignedUrls;
use Tests\Concerns\CreatesVerifiedUser;
use Tests\TestCase;

class EmailVerificationControllerTest extends TestCase
{
    use ApiAssertions;
    use BuildsSignedUrls;
    use CreatesVerifiedUser;
    use RefreshDatabase;

    private const string EMAIL_RESEND_API = '/api/v1/email/resend';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUnverifiedUser([
            'email' => 'verify@example.com',
            'unique_nickname' => 'verify_controller',
        ]);
    }

    public function test_it_verifies_email_successfully(): void
    {
        $response = $this->getJson($this->verificationUrl($this->user));

        $this->assertApiSuccess($response, 200, __('messages.email-confirmed'));
        $response->assertJsonStructure(['data' => ['token']]);
        $this->assertNotEmpty($response->json('data.token'));
        $this->user->refresh();
        $this->assertNotNull($this->user->email_verified_at);
    }

    public function test_it_returns_error_on_invalid_hash_with_valid_signature(): void
    {
        $wrongHash = hash('sha256', 'wrong@example.com');
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $this->user->id, 'hash' => $wrongHash]
        );

        $response = $this->getJson($url);

        $this->assertApiError($response, 403);
        $response->assertJsonPath('success', false);
    }

    public function test_it_returns_403_on_expired_link(): void
    {
        $response = $this->getJson($this->expiredVerificationUrl($this->user));

        $this->assertApiForbidden($response, __('exceptions.'.InvalidConfirmationLinkException::class));
    }

    public function test_it_returns_404_when_user_not_found(): void
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => 99999, 'hash' => hash('sha256', 'test@example.com')]
        );

        $this->getJson($url)->assertStatus(404)->assertJsonPath('success', false);
    }

    public function test_it_returns_400_when_email_already_verified(): void
    {
        $this->user->markEmailAsVerified();

        $response = $this->getJson($this->verificationUrl($this->user));

        $this->assertApiError($response, 400, __('exceptions.'.EmailAlreadyVerifiedException::class));
    }

    public function test_it_does_not_create_new_token_when_email_already_verified(): void
    {
        $this->user->markEmailAsVerified();
        $initialCount = $this->user->tokens()->count();

        $this->getJson($this->verificationUrl($this->user));

        $this->user->refresh();
        $this->assertSame($initialCount, $this->user->tokens()->count());
    }

    public function test_it_resends_verification_email_successfully(): void
    {
        $this->assertApiSuccess(
            $this->postJson(self::EMAIL_RESEND_API, ['email' => $this->user->email]),
            200,
            __('messages.email-resend')
        );
    }

    public function test_it_returns_success_even_if_email_not_found_on_resend(): void
    {
        $this->assertApiSuccess(
            $this->postJson(self::EMAIL_RESEND_API, ['email' => 'nonexistent@example.com']),
            200,
            __('messages.email-resend')
        );
    }

    public function test_it_returns_400_when_resending_to_verified_email(): void
    {
        $this->user->markEmailAsVerified();

        $this->assertApiError(
            $this->postJson(self::EMAIL_RESEND_API, ['email' => $this->user->email]),
            400,
            __('exceptions.'.EmailAlreadyVerifiedException::class)
        );
    }

    public function test_it_validates_email_on_resend(): void
    {
        $this->assertApiValidationErrors(
            $this->postJson(self::EMAIL_RESEND_API, ['email' => 'not-an-email']),
            ['email']
        );
    }

    public function test_it_requires_email_on_resend(): void
    {
        $this->assertApiValidationErrors(
            $this->postJson(self::EMAIL_RESEND_API, []),
            ['email']
        );
    }

    public function test_it_rate_limits_email_resend(): void
    {
        $this->postJson(self::EMAIL_RESEND_API, ['email' => $this->user->email])->assertStatus(200);
        $this->postJson(self::EMAIL_RESEND_API, ['email' => $this->user->email])->assertStatus(429);
    }

    public function test_it_returns_token_on_successful_verification(): void
    {
        $response = $this->getJson($this->verificationUrl($this->user));

        $token = $response->json('data.token');
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
        $this->assertStringContainsString('|', $token);
        $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $this->user->id]);
    }

    public function test_it_rejects_unsigned_verification_url(): void
    {
        $response = $this->getJson($this->unsignedVerificationUrl($this->user));

        $this->assertApiForbidden($response, __('exceptions.'.InvalidConfirmationLinkException::class));
    }
}
