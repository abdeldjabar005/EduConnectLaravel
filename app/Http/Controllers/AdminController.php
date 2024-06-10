<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:support');
    }

    public function dashboard()
    {
        $schools = School::whereHas('verificationRequests')->with('verificationRequests')->get();

        $schoolsCount = School::count();
        $classesCount = SchoolClass::count();
        $teachersCount = User::where('role', 'teacher')->count();
        $usersCount = User::count();

        return view('admin.dashboard', compact('schools', 'schoolsCount', 'classesCount', 'teachersCount', 'usersCount'));
    }
    public function verifySchool(Request $request, School $school)
{
    $user = $request->user();

    if ($user->role !== 'support') {
        return response()->json(['error' => 'Only the support can verify the school'], 403);
    }
    if ($school->verified) {
        return response()->json(['message' => 'This school has already been verified'], 409);
    }

    if (!$school->verified && !$school->verificationRequests()->exists()) {
        return response()->json(['message' => 'This school has been rejected and cannot be verified'], 409);
    }

    $school->verified = true;
    $school->save();
    $school->verificationRequests()->delete();

    return response()->json([
        'message' => 'School verified successfully',
        'school_id' => $school->id
    ]);
}
public function rejectSchool(Request $request, School $school)
{
    $user = $request->user();

    // Only support role can reject the school verification request
    if ($user->role !== 'support') {
        return response()->json(['error' => 'Only the support can reject the school verification request'], 403);
    }
    if (!$school->verified && !$school->verificationRequests()->exists()) {
        return response()->json(['message' => 'This school has already been rejected'], 409);
    }

    if ($school->verified) {
        return response()->json(['message' => 'This school has been verified and cannot be rejected'], 409);
    }
    $school->verificationRequests()->delete();
    $school->verification_request_sent = false;
    $school->save();

    return response()->json([
        'message' => 'School rejected successfully',
        'school_id' => $school->id,
    ]);}
}

