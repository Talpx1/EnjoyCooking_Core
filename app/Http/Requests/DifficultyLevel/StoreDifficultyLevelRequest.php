<?php

namespace App\Http\Requests\DifficultyLevel;

use App\Enums\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class StoreDifficultyLevelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can(Permissions::STORE_DIFFICULTY_LEVEL->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:difficulty_levels,name']
        ];
    }
}
