<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mentor;

use App\Application\Mentor\CreateNewMentor;
use App\Application\Mentor\DeleteMentor;
use App\Application\Mentor\GetMentor;
use App\Application\Mentor\GetUserMentors;
use App\Application\Mentor\UpdateMentor;
use App\Domain\Mentor\RequestData\CreateNewMentorRequestData;
use App\Domain\Mentor\RequestData\UpdateMentorRequestData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Base\AuthenticatedRequest;
use App\Http\Requests\Mentor\StoreMentorRequest;
use App\Http\Requests\Mentor\UpdateMentorRequest;
use App\Http\Resources\Mentor\MentorItemResource;
use App\Http\Resources\Mentor\MentorResource;
use App\Http\Responses\ApiResponse;
use App\Support\Http\HttpCode;
use Illuminate\Http\JsonResponse;

final class MentorController extends Controller
{
    public function index(AuthenticatedRequest $request, GetUserMentors $getUserMentors): JsonResponse
    {
        $mentors = $getUserMentors->run($request->getDomainUser()->id);

        return ApiResponse::success(data: MentorItemResource::collection($mentors));
    }

    public function store(StoreMentorRequest $request, CreateNewMentor $createNewMentor): JsonResponse
    {
        $requestData = CreateNewMentorRequestData::fromArray($request->validated());
        $mentor = $createNewMentor->run($requestData, $request->getDomainUser()->id);

        return ApiResponse::success(
            message: __('messages.mentor.created'),
            data: MentorResource::make($mentor),
            code: HttpCode::Created,
        );
    }

    public function show(AuthenticatedRequest $request, int $mentorId, GetMentor $getMentor): JsonResponse
    {
        $mentorDomain = $getMentor->run($mentorId, $request->getDomainUser()->id);
        return ApiResponse::success(data: MentorResource::make($mentorDomain));
    }

    public function update(UpdateMentorRequest $request, int $mentorId, UpdateMentor $updateMentor): JsonResponse
    {
        $requestData = UpdateMentorRequestData::fromArray($request->validated());
        $mentorDomain = $updateMentor->run($requestData, $mentorId, $request->getDomainUser()->id);

        return ApiResponse::success(
            message: __('messages.mentor.updated'),
            data: MentorResource::make($mentorDomain),
        );
    }

    public function destroy(AuthenticatedRequest $request, int $mentorId, DeleteMentor $deleteMentor): JsonResponse
    {
        $deleteMentor->run($mentorId, $request->getDomainUser()->id);

        return ApiResponse::success(
            message: __('messages.mentor.deleted'),
            code: HttpCode::NoContent
        );
    }
}
