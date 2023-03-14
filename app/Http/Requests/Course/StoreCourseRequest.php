<?php

namespace App\Http\Requests\Course;

use App\Enums\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can(Permissions::STORE_COURSE->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:courses,name']
        ];
    }
}
