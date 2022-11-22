<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Http\Requests\Award\StoreAwardRequest;
use App\Http\Requests\Award\UpdateAwardRequest;
use App\Models\Award;
use App\Utils\ImageUtils;

class AwardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        return Award::paginate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Award\StoreAwardRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAwardRequest $request){
        if(!$request->user()->can(Permissions::STORE_AWARD->value)) abort(403);

        $data = $request->validated();

        $path = config('upload.award.save_path') . uniqid(time().'_');
        $extensions = explode(',', config('upload.award.save_as'));
        ImageUtils::saveWithMultipleExtensions($data['icon'], 'public', $path, $extensions, config('upload.award.save_width'), config('upload.award.save_height'));

        unset($data['icon']);
        $data['icon_path'] = $path;

        $award = Award::create($data);

        return response($award, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\Response
     */
    public function show(Award $award)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Award\UpdateAwardRequest  $request
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAwardRequest $request, Award $award)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\Response
     */
    public function destroy(Award $award)
    {
        //
    }
}
