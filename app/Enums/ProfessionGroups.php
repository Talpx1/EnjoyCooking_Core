<?php

namespace App\Enums;

use App\Enums\Traits\EnumAsArray;
use App\Enums\Traits\NormalizeNames;

enum ProfessionGroups: int {

    use EnumAsArray, NormalizeNames;

    case AGRICULTURE_AND_FARMING = 1;
    case ENVIRONMENTAL_PROTECTION = 2;
    case ADMINISTRATION_FINANCE_AND_BUSINESS_CONTROL = 3;
    case ARTISTIC_CRAFTSMANSHIP = 4;
    case COMMERCIAL_BUSINESS = 5;
    case AUDIOVISUAL_AND_ENTERTAINMENT = 6;
    case CULTURAL_HERITAGE = 7;
    case CHEMISTRY = 8;
    case COMMERCIAL_AND_MARKETING = 9;
    case COMMUNICATION_AND_JOURNALISM = 10;
    case CONSTRUCTION_AND_URBAN_PLANNING = 11;
    case EDUCATION_AND_FORMATION = 12;
    case ARMED_FORCES_AND_SECURITY = 13;
    case HUMAN_RESOURCES_MANAGEMENT = 14;
    case GRAPHICS_AND_PUBLISHING = 15;
    case LARGE_DISTRIBUTION = 16;
    case AGRI_FOOD_INDUSTRY = 17;
    case IT_AND_ELECTRONICS = 18;
    case METALWORKING = 19;
    case FASHION_AND_CLOTHING = 20;
    case NON_PROFIT = 21;
    case PUBLIC_ADMINISTRATION = 22;
    case ADVERTISING = 23;
    case CATERING_AND_FOOD = 24;
    case FINANCIAL_AND_INSURANCE_SERVICES = 25;
    case HEALTH_SERVICES = 26;
    case SOCIAL_SERVICES = 27;
    case SPORT = 28;
    case TELECOMMUNICATIONS = 29;
    case TRANSPORTATION = 30;
    case TOURISM_HOSPITALITY_AND_FREE_TIME = 31;
    case OTHER = 32;
    case UNOCCUPIED = 33;
}
