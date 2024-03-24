<?php
namespace App\Http\Controllers\Api\Auth;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group OTP
 *
 * APIs for managing OTP
 * */

class OtpController extends Controller
{
public function verify(Request $request)
{
    $request->validate([
        'email' => 'required|string|email',
        'otp' => 'required|string',
    ]);

    $otp = DB::table('otp')
        ->join('users', 'users.id', '=', 'otp.user_id')
        ->where('users.email', $request->email)
        ->where('otp.otp', $request->otp)
        ->first();

    if (!$otp || $otp->expires_at < now()) {
        return response()->json(['message' => 'Invalid or expired OTP'], 400);
    }

    $user = User::find($otp->user_id);
    $user->is_verified = true;
    $user->save();

    DB::table('otp')->where('id', $otp->id)->delete();

    $token = $user->createToken('auth_token')->plainTextToken;

    return (new UserResource($user))->additional(['token' => $token]);
}}
