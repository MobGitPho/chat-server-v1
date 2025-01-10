<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatusCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
            'filename' => 'sometimes|nullable|string',
        ]);

        // If data not valid return error
        if ($validator->fails()) {
            return $this->sendResponse(
                $validator->errors(),
                ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'File validation failed',
            );
        }

        /** @var UploadedFile $file */
        $file = $request->file('file');

        $path = null;
        if ($request->has('filename') && !is_null($request->input('filename'))) {
            $path = $file->storeAs($request->input('folder') ?? 'uploads', $request->input('filename'), 'public');
        } else {
            $path = $file->store($request->input('folder') ?? 'uploads', 'public');
        }

        return $this->sendResponse(
            ['path' => $path, 'url' => Storage::disk('public')->url($path)],
            ResponseStatusCode::OK,
            'File uploaded successfully',
        );
    }

    public function replace(Request $request)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'current_path' => 'required',
            'file' => 'required|file',
        ]);

        // If data not valid return error
        if ($validator->fails()) {
            return $this->sendResponse(
                $validator->errors(),
                ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'Validation failed',
            );
        }

        if (Storage::disk('public')->delete($request->input('current_path'))) {
            /** @var UploadedFile $file */
            $newFile = $request->file('file');

            $path = $request->input('current_path');

            Storage::disk('public')->put($path, $newFile);

            return $this->sendResponse(
                ['path' => $path, 'url' => Storage::disk('public')->url($path)],
                ResponseStatusCode::OK,
                'File replaced successfully',
            );
        } else {
            return $this->sendResponse(
                null,
                ResponseStatusCode::NOT_FOUND,
                'File not found',
            );
        }
    }

    public function delete(Request $request)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'path' => 'required',
        ]);

        // If data not valid return error
        if ($validator->fails()) {
            return $this->sendResponse(
                $validator->errors(),
                ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'File path required',
            );
        }

        $deleted = Storage::disk('public')->delete($request->input('path'));

        if ($deleted) {
            return $this->sendResponse(
                [
                    'path' => $request->input('path'),
                    'url' => Storage::disk('public')->url($request->input('path'))
                ],
                ResponseStatusCode::OK,
                'File deleted successfully',
            );
        } else {
            return $this->sendResponse(
                null,
                ResponseStatusCode::NOT_FOUND,
                'File not found',
            );
        }
    }
}
