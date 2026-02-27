<?php

namespace Tests\Feature\Auth;

use App\Application\User\Auth\CreateNewUser;
use App\Domain\User\Auth\RequestData\CreatingUserRequestData;
use App\Domain\User\Exceptions\Email\EmailAlreadyVerifiedException;
use App\Domain\User\User as DomainUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string EMAIL_RESEND_API = '/api/v1/email/resend';

    private const string EMAIL_VERIFY_API = '/api/v1/email/verify';

    private User $user;

    private DomainUser $domainUser;

    private string $email = 'test@example.com';

    private string $verificationUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $createUserAction = app(CreateNewUser::class);
        $requestData = new CreatingUserRequestData(
            firstName: 'Иван',
            lastName: 'Петров',
            email: $this->email,
            uniqueNickname: 'verify_controller',
            password: 'Password123!',
            middleName: null
        );

        $this->domainUser = $createUserAction->run($requestData);
        $this->user = User::query()->findOrFail($this->domainUser->id);

        $this->verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $this->domainUser->id,
                'hash' => sha1($this->domainUser->email),
            ]
        );
    }

    public function test_it_verifies_email_successfully(): void
    {
        $response = $this->getJson($this->verificationUrl);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token'],
            ])
            ->assertJson([
                'success' => true,
                'message' => __('messages.email-confirmed'),
            ]);

        $this->user->refresh();
        $this->assertNotNull($this->user->email_verified_at);

        $token = $response->json('data.token');
        $this->assertNotEmpty($token);
    }

    public function test_it_returns_error_on_invalid_signature(): void
    {
        $invalidUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $this->domainUser->id,
                'hash' => 'invalid-hash',
            ]
        );

        $response = $this->getJson($invalidUrl);
        $response->assertStatus(400);
    }

    public function test_it_returns_error_on_expired_link(): void
    {
        $expiredUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->subMinutes(1),
            [
                'id' => $this->domainUser->id,
                'hash' => sha1($this->domainUser->email),
            ]
        );

        $response = $this->getJson($expiredUrl);
        $response->assertStatus(403);
    }

    public function test_it_returns_error_when_user_not_found(): void
    {
        $nonExistentUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => 99999,
                'hash' => sha1('test@example.com'),
            ]
        );

        $response = $this->getJson($nonExistentUrl);
        $response->assertStatus(404);
    }

    public function test_it_returns_error_when_email_already_verified(): void
    {
        $this->user->markEmailAsVerified();

        $response = $this->getJson($this->verificationUrl);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => __('exceptions.'.EmailAlreadyVerifiedException::class),
            ]);
    }

    public function test_it_does_not_create_new_token_when_email_already_verified(): void
    {
        $this->user->markEmailAsVerified();
        $initialTokenCount = $this->user->tokens()->count();

        $this->getJson($this->verificationUrl);
        $this->assertEquals($initialTokenCount, $this->user->tokens()->count());
    }

    public function test_it_resends_verification_email_successfully(): void
    {
        $response = $this->postJson(self::EMAIL_RESEND_API, [
            'email' => $this->email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __('messages.email-resend'),
            ]);
    }

    public function test_it_returns_success_even_if_email_not_found_on_resend(): void
    {
        $response = $this->postJson(self::EMAIL_RESEND_API, [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __('messages.email-resend'),
            ]);
    }

    public function test_it_returns_error_when_resending_to_verified_email(): void
    {
        $this->user->markEmailAsVerified();

        $response = $this->postJson(self::EMAIL_RESEND_API, [
            'email' => $this->email,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => __('exceptions.'.EmailAlreadyVerifiedException::class),
            ]);
    }

    public function test_it_validates_email_on_resend(): void
    {
        $response = $this->postJson(self::EMAIL_RESEND_API, [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_requires_email_on_resend(): void
    {
        $response = $this->postJson(self::EMAIL_RESEND_API, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_it_rate_limits_email_resend(): void
    {
        $this->postJson(self::EMAIL_RESEND_API, [
            'email' => $this->email,
        ])->assertStatus(200);

        $this->postJson(self::EMAIL_RESEND_API, [
            'email' => $this->email,
        ])->assertStatus(429);
    }

    public function test_it_returns_token_on_successful_verification(): void
    {
        $response = $this->getJson($this->verificationUrl);

        $response->assertStatus(200);

        $token = $response->json('data.token');
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
        $this->assertStringContainsString('|', $token);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->domainUser->id,
        ]);
    }

    public function test_it_uses_signed_middleware(): void
    {
        $unsignedUrl = self::EMAIL_VERIFY_API."/{$this->domainUser->id}/".sha1($this->domainUser->email);

        $response = $this->getJson($unsignedUrl);
        $response->assertStatus(403);
    }
}
