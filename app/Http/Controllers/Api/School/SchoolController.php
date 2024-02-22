<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolRequest;
use App\Http\Resources\SchoolResource;
use App\Models\School;

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

    public function joinSchool(SchoolRequest $request)
    {
        $user = $request->user();

        if ($user->school_id) {
            return response()->json(['error' => 'User is already joined to a school'], 400);
        }

        $user->school_id = $request->input('school_id');
        $user->save();
        return response()->json(['message' => 'User joined the school successfully']);
    }
}
