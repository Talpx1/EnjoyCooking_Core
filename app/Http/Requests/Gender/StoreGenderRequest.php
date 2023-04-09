<?php

namespace App\Http\Requests\Gender;

use App\Enums\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class StoreGenderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can(Permissions::STORE_GENDER->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:genders,name']
        ];
    }
}
