<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Http\Requests\Gender\StoreGenderRequest;
use App\Http\Requests\Gender\UpdateGenderRequest;
use App\Models\Gender;
use Illuminate\Support\Facades\Auth;

class GenderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Gender::paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGenderRequest $request)
    {
        if(!$request->user()->can(Permissions::STORE_GENDER->value)) abort(403);

        $data = $request->validated();

        $gender = Gender::create($data);

        return response()->json($gender, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Gender $gender){
        if(!request()->user()->can(Permissions::SHOW_GENDER->value)) abort(403);
        return response()->json($gender);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGenderRequest $request, Gender $gender)
    {
        if(!$request->user()->can(Permissions::UPDATE_GENDER->value)) abort(403);

        $data = $request->validated();

        $gender->update($data);

        return response()->json($gender->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gender $gender)
    {
        if( !(Auth::user()?->can(Permissions::DESTROY_GENDER->value) ?? false) ) abort(403, __('Unauthorized'));

        $gender->delete();

        return response()->json(status:200);
    }
}
