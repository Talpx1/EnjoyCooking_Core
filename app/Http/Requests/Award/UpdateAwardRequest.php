<?php

namespace App\Http\Requests\Award;

use App\Enums\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAwardRequest extends FormRequest{

    use AwardRequestCommon;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(){
        return $this->user()->can(Permissions::UPDATE_AWARD->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(){
        return array_merge(self::getCommonRules(), [
            'icon' => ['nullable', 'image', 'mimes:'.config('upload.award.accepted_file_types'), 'max:'.config('upload.award.max_file_size')],
            'name' => ['required', 'string', 'unique:awards,name,'.$this->award->id, 'max:255'],
        ]);
    }
}
