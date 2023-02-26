<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Http\Requests\Award\StoreAwardRequest;
use App\Http\Requests\Award\UpdateAwardRequest;
use App\Models\Award;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AwardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(){
        return response()->json(Award::paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Award\StoreAwardRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreAwardRequest $request){
        if(!$request->user()->can(Permissions::STORE_AWARD->value)) abort(403);

        $data = $request->validated();

        $path = Award::storeIcon($data['icon']);

        unset($data['icon']);
        $data['icon_path'] = $path;

        $award = Award::create($data);

        return response()->json($award, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Award $award){
        return response()->json($award);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Award\UpdateAwardRequest  $request
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateAwardRequest $request, Award $award){
        if(!$request->user()->can(Permissions::UPDATE_AWARD->value)) abort(403);

        $data = $request->validated();

        if(!empty($request?->icon) ){
            $award->deleteIconFiles();
            $path = Award::storeIcon($data['icon']);

            unset($data['icon']);
            $data['icon_path'] = $path;
        }

        $award->update($data);

        return response()->json($award->fresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Award $award){
        if( !(Auth::user()?->can(Permissions::DESTROY_AWARD->value) ?? false) ) abort(403, __('Unauthorized'));

        $award->deleteIconFiles();
        $award->delete();

        return response()->json(status:200);
    }
}
