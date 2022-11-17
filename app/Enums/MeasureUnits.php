<?php

namespace App\Enums;

use App\Enums\Traits\EnumAsArray;
use App\Enums\Traits\NormalizeNames;

enum MeasureUnits: int {

    use EnumAsArray, NormalizeNames;

    case KILOGRAM = 1;
    case GRAM = 2;
    case LITRE = 3;
    case MILLILITER = 4;
    case CENTILITER = 5;
    case TEA_SPOON = 6;
    case DESSERT_SPOON = 7;
    case TABLE_SPOON = 8;
    case CUP = 9;
    case SLICE = 10;
    case PINCH = 11;


    public function abbreviation(): string|null{
        return match($this){
            default => null,
            MeasureUnits::KILOGRAM => 'kg',
            MeasureUnits::GRAM => 'g',
            MeasureUnits::LITRE => 'L',
            MeasureUnits::MILLILITER => 'ml',
            MeasureUnits::CENTILITER => 'cl',

        };
    }

    public function description(): string|null{
        return match($this){
            default => null,
            MeasureUnits::TEA_SPOON => '~ 5 ml',
            MeasureUnits::DESSERT_SPOON => '~ 10 ml',
            MeasureUnits::TABLE_SPOON => '~ 15-20 ml',
            MeasureUnits::CUP => '~ 240-250 ml',
            MeasureUnits::PINCH => '~ 0.23 ml',
        };
    }

}
