<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Application\User\Auth\ResetPassword;
use App\Application\User\Auth\SendPasswordResetLink;
use App\Domain\User\Auth\Exceptions\InvalidResetTokenException;
use App\Domain\User\Auth\Exceptions\PasswordResetFailedException;
use App\Domain\User\Auth\RequestData\ResetPasswordRequestData;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    public function forgot(ForgotPasswordRequest $request, SendPasswordResetLink $sendPasswordResetLink): JsonResponse
    {
        $sendPasswordResetLink->run($request->getEmail());

        return ApiResponse::success(__('messages.password-reset-link'));
    }

    /**
     * @throws PasswordResetFailedException
     * @throws UserNotFoundException
     * @throws InvalidResetTokenException
     */
    public function reset(ResetPasswordRequest $request, ResetPassword $resetPassword): JsonResponse
    {
        $data = ResetPasswordRequestData::fromArray($request->validated());
        $token = $resetPassword->run($data);

        return ApiResponse::success(__('messages.password-reset-successful'), ['token' => $token]);
    }
}
