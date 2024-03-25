<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolRequest;
use App\Http\Resources\SchoolResource;
use App\Models\School;
use App\Models\SchoolJoinRequest;
use Illuminate\Http\Request;

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


        $data = $request->only('name', 'address');
        $data['admin_id'] = $user->id;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('school_images', 'public');
            $data['image'] = $path;
        }

        $school = School::create($data);
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
}
