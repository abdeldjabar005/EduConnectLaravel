<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolClassRequest;
use App\Http\Resources\SchoolClassResource;
use App\Models\JoinRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Str;

/**
 * @group School Classes
 *
 * APIs for managing school classes
 * */

class SchoolClassController extends Controller
{
    /**
     *
     * Display a listing of the classes.
     *
     * return SchoolClassResource
     */
    public function index()
    {
        $classes = SchoolClass::all();
        return SchoolClassResource::collection($classes);
    }


    /**
     *
     * Store a newly created resource in storage.
     *
     * @bodyParam name string required The name of the class.
     * @bodyParam grade_level integer required The grade level of the class.
     * @bodyParam subject string required The subject of the class.
     * @bodyParam school_id integer required The id of the school.
     *
     * return SchoolClassResource
     */
    public function store(SchoolClassRequest $request)
{
    $user = $request->user();
    $schoolId = $request->input('school_id', null);

    // Check if the user is a member of the school
    if (!$user->schools->contains($schoolId) && $schoolId !== null) {
        return response()->json(['error' => 'You are not a member of this school'], 403);
    }

    $data = $request->only('name', 'grade_level', 'subject');
    $data['teacher_id'] = $user->id;
    $data['school_id'] = $schoolId;
    $data['code'] = Str::random(10);

    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('class_images', 'public');
        $data['image'] = $path;
    } else {
        $data['image'] = 'class_images/classdefault.jpg';
    }

    $class = SchoolClass::create($data);
    Cache::forget('school.classes.' . $class->school_id);

    $user->classes()->attach($class->id);

    return response(new SchoolClassResource($class), 201);
}

    /**
     *
     * Display the specified resource.
     *
     * @urlParam id integer required The id of the class.
     *
     * return SchoolClassResource
     */
    public function show(string $id)
    {
        $class = SchoolClass::findOrFail($id);
        return response(new SchoolClassResource($class), 201);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     *
     * Display the classes for a student.
     *
     * @urlParam studentId integer required The id of the student.
     *
     * return SchoolClassResource
     */
    public function classesForStudent(string $studentId)
    {
        $student = Student::findOrFail($studentId);
        $classes = $student->classes;
        return SchoolClassResource::collection($classes);

    }
    /**
     * Update the school class.
     */
    public function update(SchoolClassRequest $request, string $id)
    {
        $class = SchoolClass::findOrFail($id);

        if ($request->user()->id !== $class->teacher_id) {
            return response()->json(['error' => 'Only the class owner can update the class'], 403);
        }
        $data = $request->only('name', 'grade_level', 'subject', 'school_id');
        $data['teacher_id'] = $request->user()->id;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('class_images', 'public');
            $data['image'] = $path;
        }
        $class->fill($data);
        $class->save();

        return response(new SchoolClassResource($class), 201);
    }

    /**
     * Remove a class.
     */
    public function destroy(string $id)
    {
        $class = SchoolClass::findOrFail($id);

        if (request()->user()->id !== $class->teacher_id) {
            return response()->json(['error' => 'Only the class owner can delete the class'], 403);
        }
        $class->joinRequests()->delete();

        $class->delete();

        return response()->json(["response" => "This class has been deleted"], 204);
    }

    public function addStudentToClass(Request $request)
    {
        $user = $request->user();
//        $student = Student::findOrFail($studentId);
        $classId = $request->input('class_id');
        $class = SchoolClass::findOrFail($classId);

//        // Check if the user is a parent
//        if ($user->role != 'parent') {
//            return response()->json(['error' => 'you are not the parent of this student'], 403);
//        }

        // Check if the parent is in the same school as the class
        if (!$user->schools->contains($class->school_id)) {
            return response()->json(['error' => 'User and class are not in the same school'], 403);
        }

//        // Check if the student is a child of the parent
//        if (!$user->students->contains($studentId)) {
//            return response()->json(['error' => 'The student is not a child of the parent'], 403);
//        }

        // Check if a join request already exists
        $existingJoinRequest = JoinRequest::where('student_id', $user->id)
            ->where('class_id', $classId)
            ->where('parent_id', $user->id)
            ->first();

        if ($existingJoinRequest) {
            return response()->json(['error' => 'A join request for this class already exists'], 403);
        }

        // Create a join request
        $joinRequest = new JoinRequest();
        $joinRequest->student_id = $user->id;
        $joinRequest->class_id = $classId;
        $joinRequest->parent_id = $user->id;
        $joinRequest->save();
        Cache::forget('school.classes.' . $class->school_id);

        return response()->json(['message' => 'Join request sent successfully']);
    }


public function approveJoinRequest(Request $request, string $joinRequestId)
{
    $user = $request->user();
    $joinRequest = JoinRequest::findOrFail($joinRequestId);
    $class = SchoolClass::findOrFail($joinRequest->class_id);

    // Check if the user is the class owner
    if ($user->id != $class->teacher_id) {
        return response()->json(['error' => 'Only the class owner can approve join requests'], 403);
    }

    // Add the user to the class
    $user->classes()->attach($class->id);

    // Delete the join request
    $joinRequest->delete();
    Cache::forget('school.classes.' . $class->school_id);

    return response()->json(['message' => 'Join request approved successfully']);
}

    // Method for teachers to reject a join request
public function rejectJoinRequest(Request $request, string $joinRequestId)
{
    $user = $request->user();
    $joinRequest = JoinRequest::findOrFail($joinRequestId);
    $class = SchoolClass::findOrFail($joinRequest->class_id);

    // Check if the user is the class owner
    if ($user->id != $class->teacher_id) {
        return response()->json(['error' => 'Only the class owner can reject join requests'], 403);
    }

    // Delete the join request
    $joinRequest->delete();
    Cache::forget('school.classes.' . $class->school_id);

    return response()->json(['message' => 'Join request rejected successfully']);
}

// Method for parents to view the status of their join requests
public function viewJoinRequests(Request $request)
{
    $user = $request->user();

    // Check if the user is a parent
//    if ($user->role != 'parent') {
//        return response()->json(['error' => 'Only parents can view join requests'], 403);
//    }

    $joinRequests = JoinRequest::where('parent_id', $user->id)->get();

    return response()->json(['joinRequests' => $joinRequests]);
}

// Method for teachers to view all join requests for their classes
public function viewAllJoinRequests(Request $request)
{
    $user = $request->user();

    // Check if the user is a teacher
    if ($user->role != 'teacher' && $user->role != 'admin') {
        return response()->json(['error' => 'Only teachers can view join requests'], 403);
    }

    $joinRequests = JoinRequest::whereHas('class', function ($query) use ($user) {
        $query->where('teacher_id', $user->id);
    })->with('parent', 'class')->get()->map(function ($joinRequest) {
        return [
            'id' => $joinRequest->id,
            'name' => $joinRequest->class->name,
            'first_name' => $joinRequest->parent->first_name,
            'last_name' => $joinRequest->parent->last_name,
            'profile_picture' => $joinRequest->parent->profile_picture ?? 'users-avatar/avatar.png',
        ];
    });

    return response()->json($joinRequests);
}
public function viewClassJoinRequests(Request $request, $classId)
{
    $user = $request->user();
    $class = SchoolClass::findOrFail($classId);

    // Check if the user is a teacher or admin
    if ($user->role != 'teacher' && $user->role != 'admin') {
        return response()->json(['error' => 'Only teachers can view join requests'], 403);
    }
    // Check if the user is the actual teacher of the class
    if ($user->id !== $class->teacher_id) {
        return response()->json(['error' => 'You are not the teacher of this class'], 403);
    }

    $joinRequests = JoinRequest::where('class_id', $classId)
        ->whereHas('class', function ($query) use ($user) {
            $query->where('teacher_id', $user->id);
        })
        ->with('parent', 'class')
        ->get()
        ->map(function ($joinRequest) {
            return [
                'id' => $joinRequest->id,
                'class_name' => $joinRequest->class->name,
                'first_name' => $joinRequest->parent->first_name,
                'last_name' => $joinRequest->parent->last_name,
                'profile_picture' => $joinRequest->parent->profile_picture ?? 'users-avatar/avatar.png',

            ];
        });

    return response()->json($joinRequests);
}
    public function joinClassUsingCode(Request $request)
{
    $code = $request->input('code');
    $class = SchoolClass::where('code', $code)->first();

    if (!$class) {
        return response()->json(['message' => 'Invalid code'], 404);
    }

    $user = $request->user();

    // Check if the user is already joined to the class
    if ($user->classes()->where('classes.id', $class->id)->exists()) {
        return response()->json(['message' => 'You have already joined this class'], 409);
    }

    $user->classes()->attach($class->id);

    return response()->json(new SchoolClassResource($class));
}
public function leaveClass(Request $request, SchoolClass $class)
{
    $user = $request->user();

    if ($user->id === $class->teacher_id) {
        return response()->json(['message' => 'The teacher cannot leave the class'], 403);
    }

    if (!$user->classes()->where('classes.id', $class->id)->exists()) {
        return response()->json(['message' => 'You are not a member of this class'], 403);
    }

    // Detach the user from the class
    $user->classes()->detach($class->id);
    Cache::forget('class.members.' . $class->id);

    return response()->json(['message' => 'You have successfully left the class']);
}
public function getClassMembers(SchoolClass $class)
{
    $user = auth()->user();

    // Check if the user is a member of the class
    if (!$user->classes()->where('classes.id', $class->id)->exists()) {
        return response()->json(['message' => 'You are not a member of this class'], 403);
    }

    $members = Cache::remember('class.members.' . $class->id, 60, function () use ($class) {
        return $class->users->map(function ($user) use ($class) {
            $children = $user->students->filter(function ($student) use ($class) {
                return $student->classes->contains($class->id);
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
                'bio' => $user->bio,
                'contact' => $user->contact_information,
                'children' => $children,
            ];
        });
    });

    return response()->json($members);
}
public function ownedClasses(Request $request)
{
    $user = $request->user();

    // Check if the user is a teacher
    if ($user->role == 'parent' ) {
        return response()->json(['error' => 'Only teachers or admins can view their owned classes'], 403);
    }

    $classes = $user->class;

    return SchoolClassResource::collection($classes);
}
public function removeMember(Request $request, SchoolClass $class, User $user)
{
    $teacher = $request->user();

    // Check if the authenticated user is the teacher of the class
    if ($teacher->id !== $class->teacher_id) {
        return response()->json(['error' => 'Only the teacher can remove members from the class'], 403);
    }

    // Check if the user to be removed is the same as the authenticated user
    if ($teacher->id === $user->id) {
        return response()->json(['error' => 'The teacher cannot remove themselves from the class'], 403);
    }

    // Check if the user is a member of the class
    if (!$user->classes()->where('classes.id', $class->id)->exists()) {
        return response()->json(['error' => 'The user is not a member of this class'], 404);
    }

    // Detach the user from the class
    $user->classes()->detach($class->id);

    return response()->json(['message' => 'The user has been removed from the class']);
}
public function associateStudentWithClass(Request $request, SchoolClass $class)
{
    $user = $request->user();
    $studentId = $request->input('student_id');

    if ($user->role != 'parent') {
        return response()->json(['error' => 'Only parents can associate a student with a class'], 403);
    }

    if (!$user->students->contains($studentId)) {
        return response()->json(['error' => 'The student is not a child of the parent'], 403);
    }

    if ($class->students()->where('students.id', $studentId)->exists()) {
        return response()->json(['error' => 'The student is already part of this class'], 403);
    }

    $class->students()->attach($studentId);
    Cache::forget('class.members.' . $class->id);
    Cache::forget('class.students.' . $class->id);

    return response()->json(['message' => 'The student has been associated with the class']);
}
public function getClassStudentsWithParents(Request $request, SchoolClass $class)
{
    $user = $request->user();

    if (!$user->classes()->where('classes.id', $class->id)->exists()) {
        return response()->json(['message' => 'You are not a member of this class'], 403);
    }

    $students = Cache::remember('class.students.' . $class->id, 60, function () use ($class) {
        return $class->students()->with('parents')->get()->map(function ($student) {
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
