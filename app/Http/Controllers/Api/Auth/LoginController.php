<?php

namespace App\Http\Controllers\Api\Auth;
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
            // Authentication passed...
            return redirect()->intended('/chatify');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

 public function forgotPassword(Request $request)
{
    $request->validate(['email' => 'required|email']);

    $user = User::where('email', $request->email)->first();

    if ($user) {

        $existingOtp = DB::table('otp')
            ->where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->first();

        if ($existingOtp) {
            return response()->json(['message' => 'You must wait until your existing OTP expires before requesting a new one.']);
        }

        $otp = rand(10000, 99999);

        DB::table('otp')->insert([
            'user_id' => $user->id,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(3),
        ]);

        Mail::to($user->email)->send(new OtpMail($otp));

        // Store the user's email in the session
        $request->session()->put('email', $user->email);

        return response()->json(['message' => 'OTP sent to your email.']);
    }

    return response()->json(['error' => 'No user found with this email address.'], 404);
}

public function validateOtp(Request $request)
{
    $request->validate(['otp' => 'required|string']);

    // Check if the OTP has already been validated
    if ($request->session()->get('otp_validated')) {
        return response()->json(['message' => 'This OTP has already been validated.'], 400);
    }

    $email = $request->session()->get('email');

    $otp = DB::table('otp')
        ->join('users', 'users.id', '=', 'otp.user_id')
        ->where('users.email', $email)
        ->where('otp.otp', $request->otp)
        ->first();

    if (!$otp || $otp->expires_at < now()) {
        return response()->json(['message' => 'Invalid or expired OTP'], 400);
    }

    // If the OTP is valid, store the user_id in the session and mark the OTP as validated
    $request->session()->put('user_id', $otp->user_id);
    $request->session()->put('otp_validated', true);

    return response()->json(['message' => 'OTP validated successfully. You can now reset your password.']);
}

public function resetPassword(Request $request)
{
    $request->validate(['password' => 'required|string|confirmed']);

    // Retrieve the user_id from the session
    $user_id = $request->session()->get('user_id');

    $user = User::find($user_id);

    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
    }

    // Check if the OTP has already been used
    $usedOtp = DB::table('otp')->where('user_id', $user_id)->first();

    if (!$usedOtp) {
        return response()->json(['message' => 'you already reset your password.'], 400);
    }

    $user->password = Hash::make($request->password);
    $user->save();

    // Mark the OTP as used in the database
    DB::table('otp')->where('user_id', $user_id)->delete();

    return response()->json(['message' => 'Password reset successful.']);
}
}
