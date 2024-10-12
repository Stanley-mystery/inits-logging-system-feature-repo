<?php

namespace App\Services;

use App\Models\User;
use App\Supports\Interfaces\AuthenticationServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class AuthenticationService implements AuthenticationServiceInterface
{

    /**
     * @param string $email
     * @param  string $password
     * @return string[]
     * @return string $token
     */
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password))
        {
            return ['email' => 'The provided credentials are incorrect.', 'status' => 400];
        }

        Auth::login($user);

        $token =  $user->createToken("inits-staff-user-token")->plainTextToken;

        return [
            $user,
            $token,
            "status" => 200
        ];
    }

  /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request): void
{
    Auth::logout();
     
    $request->session()->invalidate();
 
    $request->session()->regenerateToken();

}
}
