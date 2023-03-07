<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Http\Requests\Badge\StoreBadgeRequest;
use App\Http\Requests\Badge\UpdateBadgeRequest;
use App\Models\Badge;
use Illuminate\Support\Facades\Auth;

class BadgeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(){
        return response()->json(Badge::paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Badge\StoreBadgeRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreBadgeRequest $request){
        if(!$request->user()->can(Permissions::STORE_BADGE->value)) abort(403);

        $data = $request->validated();

        $badge = Badge::create($data);

        return response()->json($badge, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Badge  $badge
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Badge $badge){
        return response()->json($badge);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Badge\UpdateBadgeRequest  $request
     * @param  \App\Models\Badge  $badge
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateBadgeRequest $request, Badge $badge){
        if(!$request->user()->can(Permissions::UPDATE_BADGE->value)) abort(403);

        $data = $request->validated();

        $badge->update($data);

        return response()->json($badge->fresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Badge  $badge
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Badge $badge){
        if( !(Auth::user()?->can(Permissions::DESTROY_BADGE->value) ?? false) ) abort(403, __('Unauthorized'));

        $badge->delete();

        return response()->json(status:200);
    }
}
