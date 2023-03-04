<?php

namespace App\Http\Requests\Award;

trait AwardRequestCommon{
    private function getCommonRules(): array{
        return [
            'price' => ['required','numeric', 'min:0']
        ];
    }
}
