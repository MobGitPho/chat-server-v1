<?php

namespace App\Enums;

enum ResponseErrorCode: string
{
    case FORM_INVALID_DATA = 'form/invalid_data';

        // AUTH ERROR CODES
    case AUTH_LOGIN_FAILED = 'auth/login_failed';
    case AUTH_REGISTRATION_FAILED = 'auth/registration_failed';
    case AUTH_MISSING_DATA = 'auth/missing_data';
    case AUTH_WRONG_PASSWORD = 'auth/wrong_password';
    case AUTH_USER_DISABLED = 'auth/user_disabled';
    case AUTH_USER_NOT_FOUND = 'auth/user_not_found';
    case AUTH_PROFILE_TYPE_NOT_FOUND = 'auth/profile_type_not_found';
    case AUTH_EMAIL_ALREADY_USED = 'auth/email_already_used';
    case AUTH_EMAIL_ALREADY_VERIFIED = 'auth/email_already_verified';
    case AUTH_PROFILE_ALREADY_EXISTS = 'auth/profile_already_exists';
    case AUTH_PROFILE_NOT_FOUND = 'auth/profile_not_found';
    case AUTH_INVALID_TOKEN = 'auth/invalid_token';
    case AUTH_CODE_SENDING_FAILED = 'auth/code_sending_failed';
    case AUTH_CODE_VERIFICATION_FAILED = 'auth/code_verification_failed';
    case AUTH_CODE_EXPIRED = 'auth/code_expired';
}
