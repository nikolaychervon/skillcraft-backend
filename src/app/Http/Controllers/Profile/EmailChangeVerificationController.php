<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Domain\User\Exceptions\Email\InvalidConfirmationLinkException;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\User\Profile\Actions\VerifyEmailChangeAction;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class EmailChangeVerificationController extends Controller
{
    /**
     * @throws InvalidConfirmationLinkException
     * @throws UserNotFoundException
     */
    public function verify(int $id, string $hash, VerifyEmailChangeAction $action): JsonResponse
    {
        $action->run($id, $hash);
        return ApiResponse::success(message: __('messages.email-change-confirmed'));
    }
}
