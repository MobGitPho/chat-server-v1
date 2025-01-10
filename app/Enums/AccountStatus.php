<?php

namespace App\Enums;

enum AccountStatus: int
{
    case DISABLED = 0;
    case ENABLED = 1;
}
