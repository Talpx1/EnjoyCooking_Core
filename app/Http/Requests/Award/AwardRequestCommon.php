<?php

namespace App\Http\Requests\Award;

trait AwardRequestCommon{
    private function getCommonRules(): array{
        return [
            'icon' => ['required', 'image', 'mimes:'.config('upload.award.accepted_file_types'), 'max:'.config('upload.award.max_file_size')],
            'price' => ['nullable', 'numeric', 'min:0']
        ];
    }
}
