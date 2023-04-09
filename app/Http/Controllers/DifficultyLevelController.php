<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Http\Requests\DifficultyLevel\StoreDifficultyLevelRequest;
use App\Http\Requests\DifficultyLevel\UpdateDifficultyLevelRequest;
use App\Models\DifficultyLevel;
use Illuminate\Support\Facades\Auth;

class DifficultyLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(DifficultyLevel::paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDifficultyLevelRequest $request)
    {
        if(!$request->user()->can(Permissions::STORE_DIFFICULTY_LEVEL->value)) abort(403);

        $data = $request->validated();

        $difficultyLevel = DifficultyLevel::create($data);

        return response()->json($difficultyLevel, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DifficultyLevel $difficultyLevel)
    {
        return response()->json($difficultyLevel);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDifficultyLevelRequest $request, DifficultyLevel $difficultyLevel)
    {
        if(!$request->user()->can(Permissions::UPDATE_DIFFICULTY_LEVEL->value)) abort(403);

        $data = $request->validated();

        $difficultyLevel->update($data);

        return response()->json($difficultyLevel->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DifficultyLevel $difficultyLevel){
        if( !(Auth::user()?->can(Permissions::DESTROY_DIFFICULTY_LEVEL->value) ?? false) ) abort(403, __('Unauthorized'));

        $difficultyLevel->delete();

        return response()->json(status:200);
    }
}
