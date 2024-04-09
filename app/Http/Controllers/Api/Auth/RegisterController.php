<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * @group Authenticating requests
 *
 * APIs for managing registration
 * */

class RegisterController extends Controller
{
    /**
     *
     * Register a new user.
     *
     * @bodyParam first_name string required The first name of the user.
     * @bodyParam last_name string required The last name of the user.
     * @bodyParam email string required The email of the user.
     * @bodyParam role string required The role of the user.
     * @bodyParam password string required The password of the user.
     *
     * @response {
     *  "id": 1,
     *  "first_name": "John",
     *  "last_name": "Doe",
     *  "email": "john.doe@example.com",
     *  "role": "admin",
     *  "token": "auth_token"
     * }
     */
    public function register(RegisterRequest $request)
    {
        $data =[
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'is_verified' => false,
        ];

        $data['profile_picture'] = 'users-avatar/avatar.png';
        $user = User::create($data);

        $token = $user->createToken('auth_token')->plainTextToken;

        $otpCode = rand(10000, 99999);

        DB::table('otp')->insert([
            'user_id' => $user->id,
            'otp' => $otpCode,
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new OtpMail($otpCode));


        return (new UserResource($user))->additional(['token' => $token]);

    }
}
