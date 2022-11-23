<?php

namespace App\Http\Requests\Award;

trait AwardRequestCommon{
    private function getCommonRules(): array{
        return [
            'price' => ['nullable', 'numeric', 'min:0']
        ];
    }
}
