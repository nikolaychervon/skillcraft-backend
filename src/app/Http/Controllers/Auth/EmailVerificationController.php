<?php

namespace App\Http\Controllers\Auth;

use App\Application\Auth\Assemblers\ResendEmailDTOAssembler;
use App\Application\Shared\Exceptions\User\Email\EmailAlreadyVerifiedException;
use App\Application\Shared\Exceptions\User\Email\InvalidConfirmationLinkException;
use App\Application\Shared\Exceptions\User\UserNotFoundException;
use App\Domain\Auth\Actions\Email\ResendEmailAction;
use App\Domain\Auth\Actions\Email\VerifyEmailAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendEmailRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class EmailVerificationController extends Controller
{
    public function __construct(private readonly ResendEmailDTOAssembler $resendEmailDTOAssembler)
    {
    }

    /**
     * @param int $id
     * @param string $hash
     * @param VerifyEmailAction $verifyEmailAction
     * @return JsonResponse
     *
     * @throws EmailAlreadyVerifiedException
     * @throws InvalidConfirmationLinkException
     * @throws UserNotFoundException
     */
    public function verify(int $id, string $hash, VerifyEmailAction $verifyEmailAction): JsonResponse
    {
        $token = $verifyEmailAction->run($id, $hash);

        return ApiResponse::success(
            message: __('messages.email-confirmed'),
            data: ['token' => $token]
        );
    }

    /**
     * @param ResendEmailRequest $request
     * @param ResendEmailAction $resendEmailAction
     * @return JsonResponse
     *
     * @throws EmailAlreadyVerifiedException
     */
    public function resend(ResendEmailRequest $request, ResendEmailAction $resendEmailAction): JsonResponse
    {
        $resendEmailDTO = $this->resendEmailDTOAssembler->assemble($request->validated());
        $resendEmailAction->run($resendEmailDTO);

        return ApiResponse::success(__('messages.email-resend'));
    }
}
