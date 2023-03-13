<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request){
        return response()->json(Category::whereNameLike($request?->search)->paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Category\StoreCategoryRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCategoryRequest $request){
        if(!$request->user()->can(Permissions::STORE_CATEGORY->value)) abort(403);

        $data = $request->validated();

        $category = Category::create($data);

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Category $category){
        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Category\UpdateCategoryRequest  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCategoryRequest $request, Category $category){
        if(!$request->user()->can(Permissions::UPDATE_CATEGORY->value)) abort(403);

        $data = $request->validated();

        $category->update($data);

        return response()->json($category->fresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Category $category){
        if( !(Auth::user()?->can(Permissions::DESTROY_CATEGORY->value) ?? false) ) abort(403, __('Unauthorized'));

        $category->delete();

        return response()->json(status:200);
    }

    /**
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function subcategories(Category $category){
        return response()->json($category->subcategories()->paginate());
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function firstLevel(){
        return response()->json(Category::where('parent_category_id', null)->paginate());
    }
}
