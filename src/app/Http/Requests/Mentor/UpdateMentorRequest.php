<?php

declare(strict_types=1);

namespace App\Http\Requests\Mentor;

use App\Application\Shared\Constants\LevelsConstants;
use App\Application\Shared\Constants\MentorPersonaConstants;
use App\Http\Requests\Base\AuthenticatedRequest;
use Illuminate\Validation\Rule;

final class UpdateMentorRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $mentorId = $this->route('mentor');

        return [
            'name' => 'required|string|max:100',
            'slug' => [
                'required',
                'string',
                'min:5',
                'max:100',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('mentors', 'slug')->ignore($mentorId),
            ],
            'how_to_call_me' => ['required_if:use_name_to_call_me,false', 'nullable', 'string', 'min:2', 'max:30'],
            'use_name_to_call_me' => 'required|boolean',
            'target_level' => ['required', 'string', Rule::in(LevelsConstants::LIST)],
            'mentor_persona' => ['required', 'string', Rule::in(MentorPersonaConstants::LIST)],
        ];
    }
}
