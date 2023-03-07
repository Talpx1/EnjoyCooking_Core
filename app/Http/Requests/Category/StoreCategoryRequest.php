<?php

namespace App\Http\Requests\Category;

use App\Enums\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest{

    use CategoryRequestCommon;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(){
        return $this->user()->can(Permissions::STORE_CATEGORY->value);

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return array_merge(self::getCommonRules(), [
            'name' => ['required', 'string', 'unique:categories,name', 'max:255'],
            // 'slug' => ['nullable', 'string', 'unique:categories,slug', 'max:255'],
        ]);
    }
}
