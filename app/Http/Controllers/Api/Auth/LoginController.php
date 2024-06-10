<?php

namespace App\Http\Controllers\Api\Auth;
use App\Jobs\SendOtpEmail;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @group Authenticating requests
 *
 * APIs for managing login
 * */
class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'The provided credentials are incorrect.'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return (new UserResource($user))->additional(['token' => $token]);

    }
public function loginweb(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        // Return a JSON response with a success property
        return response()->json(['success' => true]);
    }

    // Return a JSON response with an error message
    return response()->json(['message' => 'Wrong credentials.'], 401);
}
public function logout(Request $request)
{
    // Check if the user has a current token
    if ($request->user()->currentAccessToken()) {
        // Revoke the user's current token
        $request->user()->currentAccessToken()->delete();
    }

    Auth::logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return redirect('/login');
}


public function forgotPassword(Request $request)
{
    $request->validate(['email' => 'required|email']);

    $user = User::where('email', $request->email)->first();

    if ($user) {
        $otp = rand(10000, 99999);

        DB::table('otp')->insert([
            'user_id' => $user->id,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(3),
        ]);

        dispatch(new SendOtpEmail($user->email, $otp));

        $token = JWTAuth::fromUser($user);

        return response()->json(['message' => 'OTP sent to your email.', 'token' => $token]);
    }

    return response()->json(['error' => 'No user found with this email address.'], 404);
}

public function validateOtp(Request $request)
{
    $request->validate(['otp' => 'required|string', 'token' => 'required']);

    error_log('Request: ' . print_r($request->all(), true));
    $token = $request->input('token');
    $user = JWTAuth::setToken($token)->authenticate();

    $otp = DB::table('otp')
        ->where('user_id', $user->id)
        ->where('otp', $request->otp)
        ->first();

    if (!$otp || $otp->expires_at < now()) {
        return response()->json(['message' => 'Invalid or expired OTP'], 400);
    }

    return response()->json(['message' => 'OTP validated successfully. You can now reset your password.']);
}

public function resetPassword(Request $request)
{
    $request->validate(['password' => 'required|string|confirmed', 'token' => 'required']);
    error_log('Token: ' . $request->input('token'));

    $token = $request->input('token');
    $user = JWTAuth::setToken($token)->authenticate();

    $user->password = Hash::make($request->password);
    $user->save();

    DB::table('otp')->where('user_id', $user->id)->delete();

    return response()->json(['message' => 'Password reset successful.']);
}
public function changePassword(Request $request)
{
    $request->validate([
        'old_password' => 'required|string',
        'new_password' => 'required|string|confirmed',
    ]);

    $user = Auth::user();

    if (!Hash::check($request->old_password, $user->password)) {
        return response()->json(['error' => 'The old password is incorrect.'], 400);
    }

    $user->password = Hash::make($request->new_password);
    $user->save();

    return response()->json(['message' => 'Password changed successfully.']);
}

}
