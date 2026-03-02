<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Domain\User\Exceptions\Email\InvalidConfirmationLinkException;
use App\Infrastructure\Notifications\Profile\VerifyEmailChangeNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\Concerns\ApiAssertions;
use Tests\Concerns\BuildsSignedUrls;
use Tests\Concerns\CreatesVerifiedUser;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use ApiAssertions;
    use BuildsSignedUrls;
    use CreatesVerifiedUser;
    use RefreshDatabase;

    private const string PROFILE_API = '/api/v1/profile';
    private const string CHANGE_EMAIL_API = '/api/v1/profile/change-email';
    private const string CHANGE_PASSWORD_API = '/api/v1/profile/change-password';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createVerifiedUser([
            'email' => 'profile@example.com',
            'unique_nickname' => 'profile_user',
        ]);
    }

    public function test_it_requires_authentication_for_profile_routes(): void
    {
        $this->getJson(self::PROFILE_API)->assertStatus(401);
        $this->putJson(self::PROFILE_API, [])->assertStatus(401);
        $this->postJson(self::CHANGE_EMAIL_API, [])->assertStatus(401);
        $this->postJson(self::CHANGE_PASSWORD_API, [])->assertStatus(401);
    }

    public function test_it_returns_authenticated_user_profile(): void
    {
        $response = $this->actingAs($this->user)->getJson(self::PROFILE_API);

        $this->assertApiSuccess($response);
        $response->assertJsonStructure([
            'success', 'message',
            'data' => ['id', 'first_name', 'last_name', 'middle_name', 'unique_nickname', 'email', 'email_verified_at'],
        ]);
    }

    public function test_it_updates_profile_data(): void
    {
        $payload = [
            'first_name' => 'Пётр',
            'last_name' => 'Иванов',
            'middle_name' => 'Сергеевич',
            'unique_nickname' => 'profile_user_new',
        ];

        $response = $this->actingAs($this->user)->putJson(self::PROFILE_API, $payload);

        $this->assertApiSuccess($response, 200, __('messages.profile-updated'));
        $this->assertDatabaseHas('users', array_merge(['id' => $this->user->id], $payload));
    }

    public function test_it_updates_profile_with_empty_middle_name_to_null(): void
    {
        $this->user->update(['middle_name' => 'Иванович']);

        $this->actingAs($this->user)->putJson(self::PROFILE_API, [
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'middle_name' => '',
            'unique_nickname' => $this->user->unique_nickname,
        ])->assertStatus(200);

        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'middle_name' => null]);
    }

    public function test_it_validates_profile_update_fields(): void
    {
        $this->assertApiValidationErrors(
            $this->actingAs($this->user)->putJson(self::PROFILE_API, [
                'first_name' => 'Иван',
                'last_name' => 'Петров',
                'middle_name' => null,
                'unique_nickname' => 'кириллица',
            ]),
            ['unique_nickname']
        );
    }

    public function test_it_requests_email_change_and_sets_pending_email(): void
    {
        Notification::fake();
        $newEmail = 'new-profile@example.com';

        $response = $this->actingAs($this->user)->postJson(self::CHANGE_EMAIL_API, ['email' => $newEmail]);

        $this->assertApiSuccess($response, 200, __('messages.email-verify'));
        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'pending_email' => $newEmail]);
        Notification::assertSentOnDemand(VerifyEmailChangeNotification::class);
    }

    public function test_it_validates_email_on_change_email(): void
    {
        $this->assertApiValidationErrors(
            $this->actingAs($this->user)->postJson(self::CHANGE_EMAIL_API, ['email' => 'not-an-email']),
            ['email']
        );
    }

    public function test_it_requires_email_on_change_email(): void
    {
        $this->assertApiValidationErrors(
            $this->actingAs($this->user)->postJson(self::CHANGE_EMAIL_API, []),
            ['email']
        );
    }

    public function test_it_verifies_email_change_by_signed_link(): void
    {
        Notification::fake();
        $newEmail = 'new-profile@example.com';
        $this->actingAs($this->user)->postJson(self::CHANGE_EMAIL_API, ['email' => $newEmail])->assertStatus(200);

        $url = $this->emailChangeVerificationUrl($this->user, $newEmail);
        $response = $this->getJson($url);

        $this->assertApiSuccess($response, 200, __('messages.email-change-confirmed'));
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => $newEmail,
            'pending_email' => null,
        ]);
    }

    public function test_it_returns_403_on_expired_email_change_link(): void
    {
        Notification::fake();
        $newEmail = 'new-profile@example.com';
        $this->actingAs($this->user)->postJson(self::CHANGE_EMAIL_API, ['email' => $newEmail])->assertStatus(200);

        $url = $this->expiredEmailChangeVerificationUrl($this->user, $newEmail);
        $response = $this->getJson($url);

        $this->assertApiForbidden($response, __('exceptions.'.InvalidConfirmationLinkException::class));
        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'pending_email' => $newEmail]);
    }

    public function test_it_returns_403_on_unsigned_email_change_link(): void
    {
        $url = $this->unsignedEmailChangeVerificationUrl($this->user->id, hash('sha256', 'any@example.com'));
        $response = $this->getJson($url);

        $this->assertApiForbidden($response, __('exceptions.'.InvalidConfirmationLinkException::class));
    }

    public function test_it_returns_404_for_email_change_verification_with_nonexistent_user(): void
    {
        $url = URL::temporarySignedRoute('profile.email-change.verify', now()->addMinutes(60), [
            'id' => 99999,
            'hash' => hash('sha256', 'any@example.com'),
        ]);

        $this->getJson($url)->assertStatus(404)->assertJsonPath('success', false);
    }

    public function test_it_returns_403_for_email_change_verification_with_invalid_hash(): void
    {
        Notification::fake();
        $newEmail = 'new-profile@example.com';
        $this->actingAs($this->user)->postJson(self::CHANGE_EMAIL_API, ['email' => $newEmail])->assertStatus(200);

        $url = URL::temporarySignedRoute('profile.email-change.verify', now()->addMinutes(60), [
            'id' => $this->user->id,
            'hash' => hash('sha256', 'wrong-email@example.com'),
        ]);
        $response = $this->getJson($url);

        $this->assertApiError($response, 403);
        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'pending_email' => $newEmail]);
    }

    public function test_it_changes_password_when_old_password_is_correct(): void
    {
        $response = $this->actingAs($this->user)->postJson(self::CHANGE_PASSWORD_API, [
            'old_password' => $this->defaultPassword(),
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $this->assertApiSuccess($response, 200, __('messages.password-changed'));
    }

    public function test_it_returns_422_when_old_password_is_incorrect(): void
    {
        $response = $this->actingAs($this->user)->postJson(self::CHANGE_PASSWORD_API, [
            'old_password' => 'WrongPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_it_validates_change_password_required_fields(): void
    {
        $this->assertApiValidationErrors(
            $this->actingAs($this->user)->postJson(self::CHANGE_PASSWORD_API, []),
            ['old_password', 'password']
        );
    }

    public function test_it_validates_password_confirmation_matches_on_change_password(): void
    {
        $this->assertApiValidationErrors(
            $this->actingAs($this->user)->postJson(self::CHANGE_PASSWORD_API, [
                'old_password' => $this->defaultPassword(),
                'password' => 'NewPassword123!',
                'password_confirmation' => 'DifferentPassword123!',
            ]),
            ['password']
        );
    }
}
