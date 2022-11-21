<?php

namespace App\Http\Requests\Award;

trait AwardRequestCommon{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(){
        return [
            'name' => ['required', 'string', 'unique:awards,name'],
            'icon' => ['required', 'image', 'mimes:'.config('upload.award.accepted_file_types'), 'max:'.config('upload.award.max_file_size')],
            'price' => ['nullable', 'numeric', 'min:0']
        ];
    }
}
