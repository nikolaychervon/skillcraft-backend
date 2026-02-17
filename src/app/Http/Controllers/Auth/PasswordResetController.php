<?php

namespace App\Http\Controllers\Auth;

use App\Application\Auth\Assemblers\ResetPasswordDTOAssembler;
use App\Application\Shared\Exceptions\User\UserNotFoundException;
use App\Domain\Auth\Actions\Password\ResetPasswordAction;
use App\Domain\Auth\Actions\Password\SendPasswordResetLinkAction;
use App\Domain\Auth\Exceptions\InvalidResetTokenException;
use App\Domain\Auth\Exceptions\PasswordResetFailedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    public function __construct(private readonly ResetPasswordDTOAssembler $resetPasswordDTOAssembler)
    {
    }

    /**
     * @param ForgotPasswordRequest $request
     * @param SendPasswordResetLinkAction $sendPasswordResetLinkAction
     * @return JsonResponse
     */
    public function forgot(
        ForgotPasswordRequest $request,
        SendPasswordResetLinkAction $sendPasswordResetLinkAction
    ): JsonResponse {
        $sendPasswordResetLinkAction->run($request->getEmail());
        return ApiResponse::success(__('messages.password-reset-link'));
    }

    /**
     * @param ResetPasswordRequest $request
     * @param ResetPasswordAction $resetPasswordAction
     * @return JsonResponse
     *
     * @throws InvalidResetTokenException
     * @throws PasswordResetFailedException
     * @throws UserNotFoundException
     */
    public function reset(ResetPasswordRequest $request, ResetPasswordAction $resetPasswordAction): JsonResponse
    {
        $resetPasswordDTO = $this->resetPasswordDTOAssembler->assemble($request->validated());
        $token = $resetPasswordAction->run($resetPasswordDTO);

        return ApiResponse::success(
            message: __('messages.password-reset-successful'),
            data: ['token' => $token],
        );
    }
}
