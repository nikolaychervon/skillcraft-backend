<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resources\Mentor;

use App\Http\Resources\Mentor\MentorResource;
use Illuminate\Http\Request;
use Tests\TestCase;
use Tests\Unit\Mentor\MentorTestFactory;

class MentorResourceTest extends TestCase
{
    public function test_it_transforms_mentor_to_array(): void
    {
        $mentor = MentorTestFactory::createMentor(1, 10, 'Test Mentor', 'test-mentor');
        $resource = MentorResource::make($mentor);
        $request = Request::create('/api/v1/mentors/1');
        $array = $resource->toArray($request);

        $this->assertSame(1, $array['id']);
        $this->assertSame(10, $array['user_id']);
        $this->assertSame('Test Mentor', $array['name']);
        $this->assertSame('test-mentor', $array['slug']);
        $this->assertSame('Middle', $array['target_level']);
        $this->assertArrayHasKey('current_level', $array);
        $this->assertSame('', $array['how_to_call_me']);
        $this->assertTrue($array['use_name_to_call_me']);
        $this->assertSame('friendly', $array['mentor_persona']);
        $this->assertSame('2024-01-01 00:00:00', $array['created_at']);
        $this->assertArrayHasKey('track', $array);
    }
}
