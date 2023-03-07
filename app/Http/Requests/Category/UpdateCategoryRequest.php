<?php

namespace App\Http\Requests\Category;

use App\Enums\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest{

    use CategoryRequestCommon;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(){
        return $this->user()->can(Permissions::UPDATE_CATEGORY->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(){
        return array_merge(self::getCommonRules(), [
            'name' => ['required', 'string', 'unique:categories,name,'.$this->category->id, 'max:255'],
            // 'slug' => ['nullable', 'string', 'unique:categories,slug,'.$this->category->id, 'max:255'],
        ]);
    }
}
