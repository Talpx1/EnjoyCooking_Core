<?php

namespace App\Http\Requests\Category;

trait CategoryRequestCommon{
    private function getCommonRules(): array{
        return [
            'parent_category_id' => ['nullable', 'exists:categories,id']
        ];
    }
}
