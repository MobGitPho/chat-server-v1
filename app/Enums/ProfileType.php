<?php

namespace App\Enums;

enum ProfileType: string
{
    case ADMIN = 'App\\Models\\AdminProfile';
    case CUSTOMER = 'App\\Models\\CustomerProfile';
}
