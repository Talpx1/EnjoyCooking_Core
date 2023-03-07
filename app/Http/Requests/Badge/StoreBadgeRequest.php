<?php

namespace App\Http\Requests\Badge;

use App\Enums\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class StoreBadgeRequest extends FormRequest
{

    use BadgeRequestCommon;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can(Permissions::STORE_BADGE->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return array_merge(self::getCommonRules(), [
            'title' => ['required', 'string', 'unique:badges,title'],
        ]);
    }
}
