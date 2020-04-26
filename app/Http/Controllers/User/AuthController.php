<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Auth\AuthResource;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        $credentials = $request->only('email', 'password');

        if ( Auth::attempt($credentials) ) {

            $user = Auth::user();
            $name = $user->name;
            $token = $user->createToken("Token $name");

            return new AuthResource($token);

        } else {
            throw new AuthenticationException();
        }

    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Successfully logged out']);
    }
}
