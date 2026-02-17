<?php

namespace App\Http\Controllers\Auth;

use App\Application\Auth\Assemblers\CreatingUserDTOAssembler;
use App\Application\Auth\Assemblers\LoginUserDTOAssembler;
use App\Domain\Auth\Actions\LoginUserAction;
use App\Domain\Auth\Actions\LogoutAllUserAction;
use App\Domain\Auth\Actions\LogoutUserAction;
use App\Domain\Auth\Actions\RegisterUserAction;
use App\Domain\Auth\Exceptions\IncorrectLoginDataException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly CreatingUserDTOAssembler $creatingUserDTOAssembler,
        private readonly LoginUserDTOAssembler $loginUserDTOAssembler,
    ) {
    }

    /**
     * @param LoginRequest $request
     * @param LoginUserAction $loginUserAction
     * @return JsonResponse
     *
     * @throws IncorrectLoginDataException
     */
    public function login(LoginRequest $request, LoginUserAction $loginUserAction): JsonResponse
    {
        $loginUserDTO = $this->loginUserDTOAssembler->assemble($request->validated());
        $token = $loginUserAction->run($loginUserDTO);

        return ApiResponse::success(
            message: __('auth.login'),
            data: ['token' => $token],
        );
    }

    /**
     * @param RegisterRequest $request
     * @param RegisterUserAction $registerUserAction
     * @return JsonResponse
     */
    public function register(RegisterRequest $request, RegisterUserAction $registerUserAction): JsonResponse
    {
        $creatingUserDTO = $this->creatingUserDTOAssembler->assemble($request->validated());
        $user = $registerUserAction->run($creatingUserDTO);

        return ApiResponse::success(
            message: __('messages.email-verify'),
            data: [
                'user_id' => $user->id,
                'email' => $user->email,
            ],
            code: ApiResponse::HTTP_CREATED,
        );
    }

    /**
     * @param Request $request
     * @param LogoutUserAction $logoutUserAction
     * @return JsonResponse
     */
    public function logout(Request $request, LogoutUserAction $logoutUserAction): JsonResponse
    {
        $logoutUserAction->run($request->user());
        return ApiResponse::success(__('auth.logout'));
    }

    /**
     * @param Request $request
     * @param LogoutAllUserAction $logoutAllUserAction
     * @return JsonResponse
     */
    public function logoutAll(Request $request, LogoutAllUserAction $logoutAllUserAction): JsonResponse
    {
        $logoutAllUserAction->run($request->user());
        return ApiResponse::success(__('auth.logout-all'));
    }
}
