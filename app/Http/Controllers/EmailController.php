<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatusCode;
use App\Mail\CommonMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmailController extends Controller
{
    public function send(Request $request)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string',
            'title' => 'required|string',
            'content' => 'required|string',
            'from' => 'sometimes|email',
            'from_name' => 'sometimes|string',
            'reply_to' => 'sometimes|email',
            'reply_to_name' => 'sometimes|string',
            'queue' => 'sometimes|boolean',
            'folder' => 'sometimes|string',
            'attachments' => 'sometimes|array',
            'attachments.*' => 'file',
            'recipients' => 'required|array',
            'recipients.*' => 'email',
            'metadata' => 'array',
            'tags' => 'array',
        ]);

        // If data not valid return error
        if ($validator->fails()) {
            return $this->sendResponse(
                $validator->errors(),
                ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'Email data validation failed',
            );
        }

        $attachments = [];

        if ($request->has('attachments')) {
            /** @var UploadedFile $file */
            foreach ($request->file('attachments') as $file) {
                // Store the file in a temporary location
                $attachments[] = $file->store($request->input('folder') ?? 'attachments', 'public');
            }
        }

        foreach ($request->input('recipients') as $email) {

            // Send email
            $commonMail = new CommonMail(
                [
                    'title' => $request->input('title'),
                    'message' => $request->input('content'),
                ],
                $attachments,
                [
                    'from' => $request->input('from'),
                    'fromName' => $request->input('from_name'),
                    'replyTo' => $request->input('reply_to'),
                    'replyToName' => $request->input('reply_to_name'),
                    'subject' => $request->input('subject'),
                    'metadata' => $request->input('metadata') ?? [],
                    'tags' => $request->input('tags') ?? [],
                ]
            );

            if ($request->input('queue')) {
                Mail::to($email)->queue($commonMail);
            } else {
                Mail::to($email)->send($commonMail);
            }
        }

        return $this->sendResponse(
            null,
            ResponseStatusCode::OK,
            'Email sent successfully',
        );
    }
}
