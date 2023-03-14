<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(){
        return response()->json(Course::paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Course\StoreCourseRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCourseRequest $request)
    {
        if(!$request->user()->can(Permissions::STORE_COURSE->value)) abort(403);

        $data = $request->validated();

        $course = Course::create($data);

        return response()->json($course, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Course $course){
        return response()->json($course);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Course\UpdateCourseRequest  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCourseRequest $request, Course $course){
        if(!$request->user()->can(Permissions::UPDATE_COURSE->value)) abort(403);

        $data = $request->validated();

        $course->update($data);

        return response()->json($course->fresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Course $course){
        if( !(Auth::user()?->can(Permissions::DESTROY_COURSE->value) ?? false) ) abort(403, __('Unauthorized'));

        $course->delete();

        return response()->json(status:200);
    }
}
