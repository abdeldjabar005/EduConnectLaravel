<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Http\Resources\SchoolClassResource;
use App\Http\Resources\StudentResource;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;


/**
 * @group Students
 *
 * APIs for managing students
 * */
class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = Student::all();
        return StudentResource::collection($students);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentRequest $request)
    {

        $data = $request->only('first_name', 'last_name', 'grade_level', 'relation');

        $student = Student::create($data);

        $request->user()->students()->attach($student->id);

        return response(new StudentResource($student), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $student = Student::findOrFail($id);
        return response(new StudentResource($student), 201);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

public function classesForStudent(string $studentId)
{
    $student = Student::findOrFail($studentId);
    $user = request()->user();

    // Check if the user is a parent
    if ($user->role != 'parent') {
        return response()->json(['error' => 'Only parents can view the classes of a student'], 403);
    }

    // Check if the student is a child of the parent
    if (!$user->students->contains($studentId)) {
        return response()->json(['error' => 'The student is not a child of the parent'], 403);
    }

    $classes = $student->classes;
    return SchoolClassResource::collection($classes);
}
    /**
     * Update the specified resource in storage.
     */
    public function update(StudentRequest $request, string $id)
    {
        $student = Student::findOrFail($id);

        if ($request->user()->id !== $student->parent_id) {
            return response()->json(['error' => 'Only the parent can update the student'], 403);
        }


        $data = $request->only('first_name', 'last_name', 'grade_level', 'relation');
        $data['parent_id'] = $request->user()->id;

        $student->fill($data);
        $student->save();

        return response(new StudentResource($student), 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $student = Student::findOrFail($id);

        if (request()->user()->id !== $student->parent_id) {
            return response()->json(['error' => 'Only the parent can delete the student'], 403);
        }

        $student->delete();

        return response()->json(["response" => "This student has been deleted"], 204);
    }
  public function childrenForParent(Request $request)
{
    $user = $request->user();

    if ($user->role != 'parent') {
        return response()->json(['error' => 'Only parents can view their children'], 403);
    }

    $students = $user->students;

    return StudentResource::collection($students);
}
}
