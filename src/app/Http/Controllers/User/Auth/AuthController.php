<?php

namespace App\Http\Controllers\User\Auth;

use App\Actions\User\Auth\AuthorizeUserAction;
use App\Actions\User\Auth\RegisterUserAction;
use App\DTO\User\CreatingUserDTO;
use App\Exceptions\User\Auth\IncorrectLoginDataException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\LoginRequest;
use App\Http\Requests\User\Auth\RegisterRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private RegisterUserAction $registerUserAction,
        private AuthorizeUserAction $authorizeUserAction,
    ) {
    }

    /**
     * @throws IncorrectLoginDataException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authorizeUserAction->run($request->getEmail(), $request->getUserPassword());

        return ApiResponse::success(
            message: __('auth.login'),
            data: ['token' => $token],
        );
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $creatingUserDTO = new CreatingUserDTO(
            firstName: $request->getFirstName(),
            lastName: $request->getLastName(),
            email: $request->getEmail(),
            uniqueNickname: $request->getUniqueNickname(),
            password: $request->getUserPassword(),
            middleName: $request->getMiddleName(),
        );

        $user = $this->registerUserAction->run($creatingUserDTO);

        return ApiResponse::success(
            message: __('messages.email-verify'),
            data: [
                'user_id' => $user->id,
                'email' => $user->email,
            ],
            code: ApiResponse::HTTP_CREATED,
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return ApiResponse::success(__('auth.logout'));
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();
        return ApiResponse::success(__('auth.logout-all'));
    }
}
