<?php

namespace App\Enums;

enum MessageStatus: int
{
    case SENT = 1;
    case DELIVERED = 2;
    case READ = 3;
    case FAILED = 4;
    case EDITED = 5;
    case ARCHIVED = 6;
}
