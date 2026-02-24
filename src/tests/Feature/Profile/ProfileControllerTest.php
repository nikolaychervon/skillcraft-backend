<?php

namespace Tests\Feature\Profile;

use App\Domain\User\Auth\Actions\CreateNewUserAction;
use App\Domain\User\Auth\DTO\CreatingUserDTO;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use App\Infrastructure\Notifications\Profile\VerifyEmailChangeNotification;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string PROFILE_API = '/api/v1/profile';
    private const string CHANGE_EMAIL_API = '/api/v1/profile/change-email';
    private const string CHANGE_PASSWORD_API = '/api/v1/profile/change-password';

    private User $user;
    private string $password = 'Password123!';

    protected function setUp(): void
    {
        parent::setUp();

        $createUserAction = app(CreateNewUserAction::class);
        $dto = new CreatingUserDTO(
            firstName: 'Иван',
            lastName: 'Петров',
            email: 'profile@example.com',
            uniqueNickname: 'profile_user',
            password: $this->password,
            middleName: null
        );
        $this->user = $createUserAction->run($dto);
        $this->user->markEmailAsVerified();
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

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'middle_name',
                    'unique_nickname',
                    'email',
                    'email_verified_at',
                ],
            ]);
    }

    public function test_it_updates_profile_data(): void
    {
        $response = $this->actingAs($this->user)->putJson(self::PROFILE_API, [
            'first_name' => 'Пётр',
            'last_name' => 'Иванов',
            'middle_name' => 'Сергеевич',
            'unique_nickname' => 'profile_user_new',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __('messages.profile-updated'),
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'first_name' => 'Пётр',
            'last_name' => 'Иванов',
            'middle_name' => 'Сергеевич',
            'unique_nickname' => 'profile_user_new',
        ]);
    }

    public function test_it_updates_profile_with_empty_middle_name_to_null(): void
    {
        $this->user->update(['middle_name' => 'Иванович']);

        $response = $this->actingAs($this->user)->putJson(self::PROFILE_API, [
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'middle_name' => '',
            'unique_nickname' => $this->user->unique_nickname,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'middle_name' => null,
        ]);
    }

    public function test_it_validates_profile_update_fields(): void
    {
        $response = $this->actingAs($this->user)->putJson(self::PROFILE_API, [
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'middle_name' => null,
            'unique_nickname' => 'кириллица',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['unique_nickname']);
    }

    public function test_it_requests_email_change_and_sets_pending_email(): void
    {
        Notification::fake();

        $newEmail = 'new-profile@example.com';

        $response = $this->actingAs($this->user)->postJson(self::CHANGE_EMAIL_API, [
            'email' => $newEmail,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __('messages.email-verify'),
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'pending_email' => $newEmail,
        ]);

        Notification::assertSentOnDemand(VerifyEmailChangeNotification::class);
    }

    public function test_it_verifies_email_change_by_signed_link(): void
    {
        Notification::fake();

        $newEmail = 'new-profile@example.com';
        $this->actingAs($this->user)->postJson(self::CHANGE_EMAIL_API, [
            'email' => $newEmail,
        ])->assertStatus(200);

        $url = URL::temporarySignedRoute(
            'profile.email-change.verify',
            now()->addMinutes(60),
            [
                'id' => $this->user->id,
                'hash' => sha1($newEmail),
            ]
        );

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __('messages.email-change-confirmed'),
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => $newEmail,
            'pending_email' => null,
        ]);
    }

    public function test_it_changes_password_when_old_password_is_correct(): void
    {
        $response = $this->actingAs($this->user)->postJson(self::CHANGE_PASSWORD_API, [
            'old_password' => $this->password,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __('messages.password-changed'),
            ]);
    }

    public function test_it_returns_error_when_old_password_is_incorrect(): void
    {
        $response = $this->actingAs($this->user)->postJson(self::CHANGE_PASSWORD_API, [
            'old_password' => 'WrongPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertStatus(422);
    }
}
