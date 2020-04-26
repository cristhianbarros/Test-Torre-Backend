<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Support\Facades\Auth;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this::withoutWrapping();

        return [
            'token_type' => 'Bearer',
            'expires_at'  => $this->token->expires_at,
            'accessToken' => $this->accessToken,
            'user' => new UserResource(Auth::user())
        ];
    }
}
