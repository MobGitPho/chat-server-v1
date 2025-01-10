<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatusCode;
use Illuminate\Http\JsonResponse;

class ServerStatusController extends Controller
{
    public function check(): JsonResponse
    {
        return $this->sendResponse(
            null,
            ResponseStatusCode::OK,
            'Server is up and running'
        );
    }
}
