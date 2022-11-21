<?php

namespace App\Http\Requests\Award;

use App\Enums\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class StoreAwardRequest extends FormRequest{

    use AwardRequestCommon;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(){
        return $this->user()->can(Permissions::STORE_AWARD->value);
    }

}
