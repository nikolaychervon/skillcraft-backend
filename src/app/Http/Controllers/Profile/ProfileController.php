<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Application\User\Profile\ChangeUserEmail;
use App\Application\User\Profile\ChangeUserPassword;
use App\Application\User\Profile\GetUserProfile;
use App\Application\User\Profile\UpdateUserProfile;
use App\Domain\User\Profile\RequestData\ChangeUserEmailRequestData;
use App\Domain\User\Profile\RequestData\ChangeUserPasswordRequestData;
use App\Domain\User\Profile\RequestData\UpdateUserProfileRequestData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Base\AuthenticatedRequest;
use App\Http\Requests\Profile\ChangeEmailRequest;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\Profile\UserProfileResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function show(AuthenticatedRequest $request, GetUserProfile $getUserProfile): JsonResponse
    {
        $user = $getUserProfile->run($request->getDomainUser());

        return ApiResponse::success(data: UserProfileResource::make($user));
    }

    public function update(UpdateProfileRequest $request, UpdateUserProfile $updateUserProfile): JsonResponse
    {
        $data = UpdateUserProfileRequestData::fromArray($request->validated());
        $user = $updateUserProfile->run($request->getDomainUser(), $data);

        return ApiResponse::success(__('messages.profile-updated'), UserProfileResource::make($user));
    }

    public function changeEmail(ChangeEmailRequest $request, ChangeUserEmail $changeUserEmail): JsonResponse
    {
        $data = ChangeUserEmailRequestData::fromArray($request->validated());
        $changeUserEmail->run($request->getDomainUser(), $data);

        return ApiResponse::success(__('messages.email-verify'), ['email' => $data->email]);
    }

    public function changePassword(ChangePasswordRequest $request, ChangeUserPassword $changeUserPassword): JsonResponse
    {
        $data = ChangeUserPasswordRequestData::fromArray($request->validated());
        $changeUserPassword->run($request->getDomainUser(), $data);

        return ApiResponse::success(__('messages.password-changed'));
    }
}
