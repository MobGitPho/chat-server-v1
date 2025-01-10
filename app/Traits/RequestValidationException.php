<?php

namespace App\Traits;

use App\Enums\ResponseErrorCode;
use App\Enums\ResponseStatusCode;
use App\Helpers\Functions;

trait RequestValidationException
{
    use ApiResponse;

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $request = request();

        if (Functions::isApiRequest($request)) {
            throw new \Illuminate\Validation\ValidationException($validator, $this->sendResponse(
                $validator->errors(),
                ResponseStatusCode::UNPROCESSABLE_ENTITY,
                'Validation failed',
                ResponseErrorCode::FORM_INVALID_DATA
            ));
        } else {
            parent::failedValidation($validator);
        }
    }
}
