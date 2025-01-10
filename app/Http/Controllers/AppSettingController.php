<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Enums\ResponseStatusCode;
use Illuminate\Http\Request;
use Str;

class AppSettingController extends Controller
{
    public function index()
    {
        return $this->sendResponse(AppSetting::all());
    }

    public function update(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            AppSetting::updateOrCreate(['key' => Str::camel($key)], ['value' => $value]);
        }

        return $this->sendResponse(
            null,
            ResponseStatusCode::OK,
            'App settings updated successfully'
        );
    }
}
