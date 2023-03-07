<?php

namespace App\Http\Requests\Badge;

trait BadgeRequestCommon{
    private function getCommonRules(): array{
        return [
            'description' => ['nullable', 'string']
        ];
    }
}
