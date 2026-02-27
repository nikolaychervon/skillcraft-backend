<?php

declare(strict_types=1);

namespace App\Http\Resources\Profile;

use App\Domain\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'middle_name' => $this->middleName,
            'unique_nickname' => $this->uniqueNickname,
            'email' => $this->email,
            'email_verified_at' => $this->emailVerifiedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
