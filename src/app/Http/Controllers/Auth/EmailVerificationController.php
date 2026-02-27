<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Application\User\Auth\ResendVerificationEmail;
use App\Application\User\Auth\VerifyEmail;
use App\Domain\User\Auth\RequestData\ResendEmailRequestData;
use App\Domain\User\Exceptions\Email\EmailAlreadyVerifiedException;
use App\Domain\User\Exceptions\Email\InvalidConfirmationLinkException;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendEmailRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class EmailVerificationController extends Controller
{
    /**
     * @throws EmailAlreadyVerifiedException
     * @throws UserNotFoundException
     * @throws InvalidConfirmationLinkException
     */
    public function verify(int $id, string $hash, VerifyEmail $verifyEmail): JsonResponse
    {
        $token = $verifyEmail->run($id, $hash);

        return ApiResponse::success(__('messages.email-confirmed'), ['token' => $token]);
    }

    /**
     * @throws EmailAlreadyVerifiedException
     */
    public function resend(ResendEmailRequest $request, ResendVerificationEmail $resendVerificationEmail): JsonResponse
    {
        $data = ResendEmailRequestData::fromArray($request->validated());
        $resendVerificationEmail->run($data);

        return ApiResponse::success(__('messages.email-resend'));
    }
}
