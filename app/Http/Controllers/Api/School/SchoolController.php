<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolRequest;
use App\Http\Resources\SchoolClassResource;
use App\Http\Resources\SchoolResource;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolJoinRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Symfony\Component\Mime\Part\TextPart;

/**
 * @group Schools
 *
 * APIs for managing schools
 * */
class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::all();
        return SchoolResource::collection($schools);
    }

    public function store(SchoolRequest $request)
{
    $user = $request->user();
    if ($user->school) {
        return response()->json(['error' => 'An admin can only own one school'], 403);
    }
    $data = $request->only('name', 'address');
    $data['admin_id'] = $user->id;
    $data['code'] = Str::random(10);
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('school_images', 'public');
        $data['image'] = $path;
    }else {
        $data['image'] = 'school_images/schooldefault.jpeg';
    }

    $school = School::create($data);

    $user->schools()->attach($school->id);

    return response(new SchoolResource($school), 201);
}
    public function show(School $school)
    {
        return response(new SchoolResource($school), 201);
    }

    public function update(SchoolRequest $request, School $school)
    {
        $user = $request->user();


        if ($user->id !== $school->admin_id) {
            return response()->json(['error' => 'Only the admin who owns the school can modify it'], 403);
        }

        $data = $request->only('name', 'address');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('school_images', 'public');
            $data['image'] = $path;
        }

        $school->update($data);
        return response(new SchoolResource($school), 201);
    }

    public function destroy(SchoolRequest $request, School $school)
    {
        $user = $request->user();

        if ($user->id !== $school->admin_id) {
            return response()->json(['error' => 'Only the admin who owns the school can delete it'], 403);
        }
        $school->delete();
        return response()->json(["response" => "This school has been deleted"], 204);
    }

    public function createSchoolJoinRequest(Request $request)
    {
        $user = $request->user();
        $schoolId = $request->input('school_id');

        // Check if the user is already a member of the school
        if ($user->schools()->where('schools.id', $schoolId)->exists()) {
            return response()->json(['error' => 'You are already a member of this school'], 403);
        }

        // Check if a join request already exists
        $existingJoinRequest = SchoolJoinRequest::where('user_id', $user->id)
            ->where('school_id', $schoolId)
            ->first();

        if ($user->school_id == $schoolId) {
            return response()->json(['error' => 'You are already a member of this school'], 403);
        }
        if ($existingJoinRequest) {
            return response()->json(['error' => 'A join request for this school already exists'], 403);
        }

        // Create a join request
        $joinRequest = new SchoolJoinRequest();
        $joinRequest->user_id = $user->id;
        $joinRequest->school_id = $schoolId;
        $joinRequest->save();

        return response()->json(['message' => 'Join request sent successfully']);
    }
    public function viewSchoolJoinRequestsForUser(Request $request)
    {

        $user = $request->user();

        $joinRequests = SchoolJoinRequest::where('user_id', $user->id)->get();

        return response()->json($joinRequests);
    }

    public function viewSchoolJoinRequests(Request $request)
    {
        $user = $request->user();

        // Only admins can view join requests
        if ($user->role != 'admin') {
            return response()->json(['error' => 'Only admins can view join requests'], 403);
        }

        $joinRequests = SchoolJoinRequest::whereHas('school', function ($query) use ($user) {
            $query->where('admin_id', $user->id);
        })->get();
        return response()->json($joinRequests);
    }

    public function approveSchoolJoinRequest(Request $request, string $joinRequestId)
    {
        $user = $request->user();
        $joinRequest = SchoolJoinRequest::findOrFail($joinRequestId);
        $school = School::findOrFail($joinRequest->school_id);

        // Only admins can approve join requests
        if ($user->role != 'admin') {
            return response()->json(['error' => 'Only admins can approve join requests'], 403);
        }
        if ($user->id != $school->admin_id) {
            return response()->json(['error' => 'Only the admin of the school can approve join requests'], 403);
        }

        $joinRequest->user->schools()->attach($school->id);

        // Delete the join request
        $joinRequest->delete();

        return response()->json(['message' => 'Join request approved successfully']);
    }

    public function rejectSchoolJoinRequest(Request $request, string $joinRequestId)
{
    $user = $request->user();
    $joinRequest = SchoolJoinRequest::findOrFail($joinRequestId);
    $school = School::findOrFail($joinRequest->school_id);

    // Only admins can reject join requests
    if ($user->role != 'admin') {
        return response()->json(['error' => 'Only admins can reject join requests'], 403);
    }

    if ($user->id != $school->admin_id) {
        return response()->json(['error' => 'Only the admin of the school can reject join requests'], 403);
    }
    $joinRequest->user->schools()->detach($school->id);

    // Delete the join request
    $joinRequest->delete();

    return response()->json(['message' => 'Join request rejected successfully']);
}
   public function joinSchoolUsingCode(Request $request)
{
    $code = $request->input('code');
    $school = School::where('code', $code)->first();

    if (!$school) {
        return response()->json(['message' => 'Invalid code'], 404);
    }

    $user = $request->user();

    // Check if the user is already joined to the school
    if ($user->schools()->where('schools.id', $school->id)->exists()) {
        return response()->json(['message' => 'You have already joined this school'], 409);
    }

    $user->schools()->attach($school->id);

    return response()->json(new SchoolResource($school));
}

public function getSchoolMembers(School $school)
{
    $user = auth()->user();

//     Check if the user is a member of the school
    if (!$user->schools()->where('schools.id', $school->id)->exists()) {
        return response()->json(['message' => 'You are not a member of this school'], 403);
    }

    $members = $school->users->map(function ($user) {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'profile_picture' => $user->profile_picture ?? 'users-avatar/avatar.png',
        ];
    });

    return response()->json($members);
}

public function getSchoolClasses(Request $request, School $school)
{
    $user = $request->user();

    // Check if the user is a member of the school
    if (!$user->schools()->where('schools.id', $school->id)->exists()) {
        return response()->json(['message' => 'You are not a member of this school'], 403);
    }

    $classes = $school->classes->map(function ($class) use ($user) {
        return [
            'class' => new SchoolClassResource($class),
            'is_member' => $class->users()->where('users.id', $user->id)->exists(),
        ];
    });

    return response()->json($classes);
}
public function removeMember(Request $request, School $school, User $user)
{
    $admin = $request->user();

    // Check if the authenticated user is the admin of the school
    if ($admin->id !== $school->admin_id) {
        return response()->json(['error' => 'Only the admin can remove members from the school'], 403);
    }

    // Check if the user to be removed is the same as the authenticated user
    if ($admin->id === $user->id) {
        return response()->json(['error' => 'The admin cannot remove themselves from the school'], 403);
    }

    // Check if the user is a member of the school
    if (!$user->schools()->where('schools.id', $school->id)->exists()) {
        return response()->json(['error' => 'The user is not a member of this school'], 404);
    }

    // Detach the user from the school
    $user->schools()->detach($school->id);

    return response()->json(['message' => 'The user has been removed from the school']);
}
public function removeClass(Request $request, School $school, SchoolClass $class)
{
    $admin = $request->user();

    if ($admin->id !== $school->admin_id) {
        return response()->json(['error' => 'Only the admin can remove classes from the school'], 403);
    }

    if ($class->school_id !== $school->id) {
        return response()->json(['error' => 'The class is not part of this school'], 404);
    }

    $class->delete();

    return response()->json(['message' => 'The class has been removed from the school']);
}
public function sendVerificationRequest(Request $request, School $school)
{
    $user = $request->user();

    if ($user->id !== $school->admin_id) {
        return response()->json(['error' => 'Only the admin can send a verification request'], 403);
    }
    // Check if a verification request has already been sent
    if ($school->verification_request_sent) {
        return response()->json(['error' => 'A verification request has already been sent for this school'], 403);
    }

    $email = $request->input('email');
    $phoneNumber = $request->input('phone_number');
    $document = $request->file('document');

    // Validate the inputs
    $request->validate([
        'email' => 'required|email',
        'phone_number' => 'required',
        'document' => 'required|file|mimes:pdf,doc,docx' // adjust this as needed
    ]);

    // Store the document
    $documentPath = $document->store('documents', 'public');

    // Send the email
    Mail::raw("EduConnect Verification request for school: {$school->name}\nEmail: {$email}\nPhone Number: {$phoneNumber}", function ($message) use ($documentPath, $document) {
        $message->to('abdeldjabar05@gmail.com')
              ->subject('School Verification Request')
              ->attach(storage_path('app/public/'.$documentPath), [
                  'as' => $document->getClientOriginalName(),
                  'mime' => $document->getClientMimeType(),
              ]);
    });

    return response()->json(['message' => 'Verification request sent']);
}
public function verifySchool(Request $request, School $school)
{
    $user = $request->user();

    if ($user->role !== 'support') {
        return response()->json(['error' => 'Only the support can verify the school'], 403);
    }

    $school->verified = true;
    $school->save();

    return response()->json(['message' => 'School verified successfully']);
}

}
