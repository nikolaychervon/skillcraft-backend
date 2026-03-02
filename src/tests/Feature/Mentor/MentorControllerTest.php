<?php

declare(strict_types=1);

namespace Tests\Feature\Mentor;

use App\Application\Shared\Constants\LevelsConstants;
use App\Application\Shared\Constants\MentorPersonaConstants;
use App\Models\Mentor as MentorModel;
use App\Models\Track;
use App\Models\User;
use Database\Seeders\MentorSeeder;
use Database\Seeders\ProgrammingLanguageSeeder;
use Database\Seeders\SpecializationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\ApiAssertions;
use Tests\Concerns\CreatesVerifiedUser;
use Tests\TestCase;

class MentorControllerTest extends TestCase
{
    use ApiAssertions;
    use CreatesVerifiedUser;
    use RefreshDatabase;

    private const string MENTORS_API = '/api/v1/mentors';

    private User $user;
    private Track $track;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SpecializationSeeder::class);
        $this->seed(ProgrammingLanguageSeeder::class);
        $this->seed(MentorSeeder::class);
        $this->user = $this->createVerifiedUser(['email' => 'mentor-feature@example.com']);
        $this->track = Track::query()->firstOrFail();
    }

    public function test_it_requires_authentication_for_mentor_routes(): void
    {
        $this->getJson(self::MENTORS_API)->assertStatus(401);
        $this->postJson(self::MENTORS_API, [])->assertStatus(401);
        $this->getJson(self::MENTORS_API . '/1')->assertStatus(401);
        $this->putJson(self::MENTORS_API . '/1', [])->assertStatus(401);
        $this->deleteJson(self::MENTORS_API . '/1')->assertStatus(401);
    }

    public function test_index_returns_empty_list_when_user_has_no_mentors(): void
    {
        $response = $this->actingAs($this->user)->getJson(self::MENTORS_API);

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data', []);
    }

    public function test_index_returns_user_mentors(): void
    {
        MentorModel::query()->create([
            'user_id' => $this->user->id,
            'track_id' => $this->track->id,
            'name' => 'My Mentor',
            'slug' => 'my-mentor-'.uniqid(),
            'target_level' => LevelsConstants::MIDDLE,
            'current_level' => LevelsConstants::UNSETTED,
            'how_to_call_me' => null,
            'use_name_to_call_me' => true,
            'mentor_persona' => MentorPersonaConstants::FRIENDLY,
        ]);

        $response = $this->actingAs($this->user)->getJson(self::MENTORS_API);

        $this->assertApiSuccess($response);
        $response->assertJsonStructure(['data' => [['id', 'name', 'slug', 'target_level', 'created_at', 'track']]]);
        $response->assertJsonPath('data.0.name', 'My Mentor');
    }

    public function test_store_creates_mentor_and_returns_201(): void
    {
        $payload = [
            'specialization_id' => $this->track->specialization_id,
            'programming_language_id' => $this->track->programming_language_id,
            'name' => 'New Mentor',
            'slug' => 'new-mentor-'.substr(uniqid(), -8),
            'use_name_to_call_me' => true,
            'target_level' => LevelsConstants::SENIOR,
            'mentor_persona' => MentorPersonaConstants::STRICT,
        ];

        $response = $this->actingAs($this->user)->postJson(self::MENTORS_API, $payload);

        $this->assertApiSuccess($response, 201, __('messages.mentor.created'));
        $response->assertJsonStructure(['data' => ['id', 'name', 'slug', 'track', 'created_at']]);
        $response->assertJsonPath('data.name', 'New Mentor');
        $this->assertDatabaseHas('mentors', [
            'user_id' => $this->user->id,
            'name' => 'New Mentor',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->postJson(self::MENTORS_API, []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['specialization_id', 'programming_language_id', 'name', 'slug', 'use_name_to_call_me', 'target_level', 'mentor_persona']);
    }

    public function test_store_rejects_duplicate_slug(): void
    {
        $slug = 'unique-slug-'.substr(uniqid(), -8);
        MentorModel::query()->create([
            'user_id' => $this->user->id,
            'track_id' => $this->track->id,
            'name' => 'Existing',
            'slug' => $slug,
            'target_level' => LevelsConstants::MIDDLE,
            'current_level' => LevelsConstants::UNSETTED,
            'how_to_call_me' => null,
            'use_name_to_call_me' => true,
            'mentor_persona' => MentorPersonaConstants::NEUTRAL,
        ]);

        $response = $this->actingAs($this->user)->postJson(self::MENTORS_API, [
            'specialization_id' => $this->track->specialization_id,
            'programming_language_id' => $this->track->programming_language_id,
            'name' => 'Another',
            'slug' => $slug,
            'use_name_to_call_me' => true,
            'target_level' => LevelsConstants::MIDDLE,
            'mentor_persona' => MentorPersonaConstants::FRIENDLY,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['slug']);
    }

    public function test_show_returns_mentor_when_owned_by_user(): void
    {
        $mentor = MentorModel::query()->create([
            'user_id' => $this->user->id,
            'track_id' => $this->track->id,
            'name' => 'Show Me',
            'slug' => 'show-me-'.uniqid(),
            'target_level' => LevelsConstants::MIDDLE,
            'current_level' => LevelsConstants::UNSETTED,
            'how_to_call_me' => null,
            'use_name_to_call_me' => true,
            'mentor_persona' => MentorPersonaConstants::NEUTRAL,
        ]);

        $response = $this->actingAs($this->user)->getJson(self::MENTORS_API . '/' . $mentor->id);

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.id', $mentor->id);
        $response->assertJsonPath('data.name', 'Show Me');
    }

    public function test_show_returns_404_when_mentor_does_not_exist(): void
    {
        $response = $this->actingAs($this->user)->getJson(self::MENTORS_API . '/99999');

        $response->assertStatus(404)->assertJsonPath('success', false);
    }

    public function test_show_returns_404_when_mentor_belongs_to_another_user(): void
    {
        $otherUser = $this->createVerifiedUser(['email' => 'other-mentor@example.com']);
        $mentor = MentorModel::query()->create([
            'user_id' => $otherUser->id,
            'track_id' => $this->track->id,
            'name' => 'Other Mentor',
            'slug' => 'other-mentor-'.uniqid(),
            'target_level' => LevelsConstants::MIDDLE,
            'current_level' => LevelsConstants::UNSETTED,
            'how_to_call_me' => null,
            'use_name_to_call_me' => true,
            'mentor_persona' => MentorPersonaConstants::NEUTRAL,
        ]);

        $response = $this->actingAs($this->user)->getJson(self::MENTORS_API . '/' . $mentor->id);

        $response->assertStatus(404)->assertJsonPath('success', false);
    }

    public function test_update_modifies_mentor_when_owned_by_user(): void
    {
        $mentor = MentorModel::query()->create([
            'user_id' => $this->user->id,
            'track_id' => $this->track->id,
            'name' => 'Original',
            'slug' => 'original-upd-'.uniqid(),
            'target_level' => LevelsConstants::MIDDLE,
            'current_level' => LevelsConstants::UNSETTED,
            'how_to_call_me' => null,
            'use_name_to_call_me' => true,
            'mentor_persona' => MentorPersonaConstants::NEUTRAL,
        ]);

        $payload = [
            'name' => 'Updated Name',
            'slug' => $mentor->slug,
            'use_name_to_call_me' => false,
            'how_to_call_me' => 'Buddy',
            'target_level' => LevelsConstants::SENIOR,
            'current_level' => LevelsConstants::MIDDLE,
            'mentor_persona' => MentorPersonaConstants::STRICT,
        ];

        $response = $this->actingAs($this->user)->putJson(self::MENTORS_API . '/' . $mentor->id, $payload);

        $this->assertApiSuccess($response, 200, __('messages.mentor.updated'));
        $response->assertJsonPath('data.name', 'Updated Name');
        $this->assertDatabaseHas('mentors', ['id' => $mentor->id, 'name' => 'Updated Name']);
    }

    public function test_update_returns_404_when_mentor_belongs_to_another_user(): void
    {
        $otherUser = $this->createVerifiedUser(['email' => 'other-upd@example.com']);
        $mentor = MentorModel::query()->create([
            'user_id' => $otherUser->id,
            'track_id' => $this->track->id,
            'name' => 'Other',
            'slug' => 'other-upd-'.uniqid(),
            'target_level' => LevelsConstants::MIDDLE,
            'current_level' => LevelsConstants::UNSETTED,
            'how_to_call_me' => null,
            'use_name_to_call_me' => true,
            'mentor_persona' => MentorPersonaConstants::NEUTRAL,
        ]);

        $response = $this->actingAs($this->user)->putJson(self::MENTORS_API . '/' . $mentor->id, [
            'name' => 'Hacked',
            'slug' => $mentor->slug,
            'use_name_to_call_me' => true,
            'target_level' => LevelsConstants::MIDDLE,
            'mentor_persona' => MentorPersonaConstants::FRIENDLY,
        ]);

        $response->assertStatus(404);
        $this->assertDatabaseHas('mentors', ['id' => $mentor->id, 'name' => 'Other']);
    }

    public function test_destroy_deletes_mentor_when_owned_by_user(): void
    {
        $mentor = MentorModel::query()->create([
            'user_id' => $this->user->id,
            'track_id' => $this->track->id,
            'name' => 'To Delete',
            'slug' => 'to-delete-'.uniqid(),
            'target_level' => LevelsConstants::MIDDLE,
            'current_level' => LevelsConstants::UNSETTED,
            'how_to_call_me' => null,
            'use_name_to_call_me' => true,
            'mentor_persona' => MentorPersonaConstants::NEUTRAL,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(self::MENTORS_API . '/' . $mentor->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('mentors', ['id' => $mentor->id]);
    }

    public function test_destroy_returns_404_when_mentor_belongs_to_another_user(): void
    {
        $otherUser = $this->createVerifiedUser(['email' => 'other-del@example.com']);
        $mentor = MentorModel::query()->create([
            'user_id' => $otherUser->id,
            'track_id' => $this->track->id,
            'name' => 'Other Delete',
            'slug' => 'other-del-'.uniqid(),
            'target_level' => LevelsConstants::MIDDLE,
            'current_level' => LevelsConstants::UNSETTED,
            'how_to_call_me' => null,
            'use_name_to_call_me' => true,
            'mentor_persona' => MentorPersonaConstants::NEUTRAL,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(self::MENTORS_API . '/' . $mentor->id);

        $response->assertStatus(404);
        $this->assertDatabaseHas('mentors', ['id' => $mentor->id]);
    }
}
