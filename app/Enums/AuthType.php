<?php

namespace App\Enums;

enum AuthType: int
{
    case UNDEFINED = 0;
    case EMAIL_PASSWORD = 1;
    case PHONE = 2;
    case GOOGLE = 3;
    case FACEBOOK = 4;
    case TWITTER = 5;
}
