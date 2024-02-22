<?php

namespace App\Http\Controllers\Api\School;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolClassRequest;
use App\Http\Resources\SchoolClassResource;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;

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


        $data = $request->only('name', 'grade_level', 'subject', 'school_id');
        $data['teacher_id'] = $user->id;
        $data['school_id'] = $user->school_id;

        $class = SchoolClass::create($data);

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
        $data['school_id'] = $request->user()->school_id;
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

        $class->delete();

        return response()->json(["response" => "This class has been deleted"], 204);
    }
}
