<?php

namespace App\Enums\Traits;

trait NormalizeNames{

    public function normalizedName(): string{
        return str_replace('_', ' ', ucfirst(strtolower($this->name)));
    }

}

