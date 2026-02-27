<?php

declare(strict_types=1);

namespace App\Http\Controllers\Profile;

use App\Application\User\Profile\VerifyEmailChange;
use App\Domain\User\Exceptions\Email\InvalidConfirmationLinkException;
use App\Domain\User\Exceptions\UserNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class EmailChangeVerificationController extends Controller
{
    /**
     * @throws UserNotFoundException
     * @throws InvalidConfirmationLinkException
     */
    public function verify(int $id, string $hash, VerifyEmailChange $verifyEmailChange): JsonResponse
    {
        $verifyEmailChange->run($id, $hash);

        return ApiResponse::success(__('messages.email-change-confirmed'));
    }
}
