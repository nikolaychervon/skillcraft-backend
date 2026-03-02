<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Resources\Mentor;

use App\Http\Resources\Mentor\MentorItemResource;
use Illuminate\Http\Request;
use Tests\TestCase;
use Tests\Unit\Mentor\MentorTestFactory;

class MentorItemResourceTest extends TestCase
{
    public function test_it_transforms_mentor_to_item_array(): void
    {
        $mentor = MentorTestFactory::createMentor(1, 10, 'Item Mentor', 'item-mentor');
        $resource = MentorItemResource::make($mentor);
        $request = Request::create('/api/v1/mentors');
        $array = $resource->toArray($request);

        $this->assertSame(1, $array['id']);
        $this->assertSame('Item Mentor', $array['name']);
        $this->assertSame('item-mentor', $array['slug']);
        $this->assertSame('Middle', $array['target_level']);
        $this->assertSame('2024-01-01 00:00:00', $array['created_at']);
        $this->assertArrayHasKey('track', $array);
        $this->assertArrayNotHasKey('user_id', $array);
        $this->assertArrayNotHasKey('how_to_call_me', $array);
        $this->assertArrayNotHasKey('mentor_persona', $array);
    }
}
