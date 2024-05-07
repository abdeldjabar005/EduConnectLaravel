<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolRequest;
use App\Http\Resources\SchoolClassResource;
use App\Http\Resources\SchoolResource;
use App\Models\JoinRequest;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\SchoolJoinRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

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
    $data['verified'] = false;
    $data['verification_request_sent'] = false;
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
    })->with('user', 'school')->get()->map(function ($joinRequest) {
        return [
            'id' => $joinRequest->id,
            'name' => $joinRequest->school->name,
            'first_name' => $joinRequest->user->first_name,
            'last_name' => $joinRequest->user->last_name,
            'profile_picture' => $joinRequest->user->profile_picture ?? 'users-avatar/avatar.png',

        ];
    });

    return response()->json($joinRequests);
}

public function viewSchoolJoinRequestsForOneSchool(Request $request, $schoolId)
{
    $user = $request->user();
    $school = School::findOrFail($schoolId);

    // Only admins can view join requests
    if ($user->role != 'admin') {
        return response()->json(['error' => 'Only admins can view join requests'], 403);
    }
    if ($user->id !== $school->admin_id) {
        return response()->json(['error' => 'You are not the admin of this school'], 403);
    }

    $joinRequests = SchoolJoinRequest::where('school_id', $schoolId)
        ->whereHas('school', function ($query) use ($user) {
            $query->where('admin_id', $user->id);
        })
        ->with('user', 'school')
        ->get()
        ->map(function ($joinRequest) {
            return [
                'id' => $joinRequest->id,
                'name' => $joinRequest->school->name,
                'first_name' => $joinRequest->user->first_name,
                'last_name' => $joinRequest->user->last_name,
                'profile_picture' => $joinRequest->user->profile_picture ?? 'users-avatar/avatar.png',
            ];
        });

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
public function leaveSchool(Request $request, School $school)
{
    $user = $request->user();

    if ($user->id === $school->admin_id) {
        return response()->json(['message' => 'The admin cannot leave the school'], 403);
    }
    if (!$user->schools()->where('schools.id', $school->id)->exists()) {
        return response()->json(['message' => 'You are not a member of this school'], 403);
    }

    $user->schools()->detach($school->id);
    Cache::forget('school.members.' . $school->id);

    return response()->json(['message' => 'You have successfully left the school']);
}
public function getSchoolMembers(School $school)
{
    $user = auth()->user();

    // Check if the user is a member of the school
    if (!$user->schools()->where('schools.id', $school->id)->exists()) {
        return response()->json(['message' => 'You are not a member of this school'], 403);
    }

    $members = Cache::remember('school.members.' . $school->id, 60, function () use ($school) {
        return $school->users->map(function ($user) use ($school) {
            $children = $user->students->filter(function ($student) use ($school) {
                return $student->schools->contains($school->id);
            })->map(function ($student) {
                return [
                    'id' => $student->id,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'relation' => $student->relation,
                    'relation_display' => $student->relation . ' of ' . $student->first_name,
                ];
            })->values();

            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role' => $user->role,
                'profile_picture' => $user->profile_picture ?? 'users-avatar/avatar.png',
                'children' => $children,
            ];
        });
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

    $classes = Cache::remember('school.classes.' . $school->id, 60, function () use ($school, $user) {
        return $school->classes->map(function ($class) use ($user) {
            $isMember = 0;
            if ($class->users()->where('users.id', $user->id)->exists()) {
                $isMember = 1;
            } else {
                // Check if there is a pending join request from the user to the class
                $joinRequest = JoinRequest::where('student_id', $user->id)
                    ->where('class_id', $class->id)
                    ->first();
                if ($joinRequest) {
                    $isMember = 2;
                }
            }
            return [
                'class' => new SchoolClassResource($class),
                'is_member' => $isMember,
            ];
        });
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
    Mail::raw("EduConnect Verification request for school : {$school->name}\nEmail: {$email}\nPhone Number: {$phoneNumber}\n with the id: {$school->id}", function ($message) use ($documentPath, $document) {
        $message->to('abdeldjabar05@gmail.com')
              ->subject('School Verification Request')
              ->attach(storage_path('app/public/'.$documentPath), [
                  'as' => $document->getClientOriginalName(),
                  'mime' => $document->getClientMimeType(),
              ]);
    });
    $school->verification_request_sent = true;
    $school->save();

    return response()->json(new SchoolResource($school));

}
public function getSchoolsWithVerificationRequest(Request $request)
{
    $user = $request->user();

    if ($user->role !== 'support') {
        return response()->json(['error' => 'Only support can view schools with verification requests'], 403);
    }

    $schools = School::where('verification_request_sent', true)->get();

    return SchoolResource::collection($schools);
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

public function associateStudentWithSchool(Request $request, School $school)
{
    $user = $request->user();
    $studentId = $request->input('student_id');

    if ($user->role != 'parent') {
        return response()->json(['error' => 'Only parents can associate a student with a school'], 403);
    }

    if (!$user->students->contains($studentId)) {
        return response()->json(['error' => 'The student is not a relative of the parent'], 403);
    }

    if ($school->students()->where('students.id', $studentId)->exists()) {
        return response()->json(['error' => 'The student is already part of this school'], 403);
    }

    $school->students()->attach($studentId);
    Cache::forget('school.members.' . $school->id);
    Cache::forget('school.students.' . $school->id);


    return response()->json(['message' => 'The student has been associated with the school']);
}
public function getSchoolStudentsWithParents(Request $request, School $school)
{
    $user = $request->user();

    if (!$user->schools()->where('schools.id', $school->id)->exists()) {
        return response()->json(['message' => 'You are not a member of this school'], 403);
    }

    $students = Cache::remember('school.students.' . $school->id, 60, function () use ($school) {
        return $school->students()->with('parents')->get()->map(function ($student) {
            return [
                'id' => $student->id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'parents' => $student->parents->map(function ($parent) {
                    return [
                        'id' => $parent->id,
                        'first_name' => $parent->first_name,
                        'last_name' => $parent->last_name,
                        'role' => $parent->role,
                        'profile_picture' => $parent->profile_picture ?? 'users-avatar/avatar.png',

                    ];
                }),
            ];
        });
    });

    return response()->json($students);
}
}
