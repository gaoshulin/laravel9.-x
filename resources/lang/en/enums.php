<?php
/**
 * Created by PhpStorm
 * USER: Administrator
 * Author: Galen
 * Date: 2023/4/27 11:07
 */

use App\Enums\StatusCode;

return [
    StatusCode::class => [
        StatusCode::ERROR => "Oops, we've got a problem, please try again later.",
        StatusCode::REQUEST_FAILED => "Request faild with response: :response",
        StatusCode::USER_UNAUTHORIZED => 'Unauthorized user.',
        StatusCode::USER_PERMISSION_DENIED => 'Sorry, you have no permission to access.',
        StatusCode::USER_LOGIN_CREDENTIALS_ERROR => 'Sorry, username or email invalid.',
        StatusCode::CONTINUE_EMAIL_SIGNUP => 'Please continue registration to set email & password.',
        StatusCode::EMAIL_NOT_VERIFY_ERROR => 'Email not verified, please check.',
        StatusCode::PERMISSION_DENIED_TO_INVITE_USER => 'Sorry, you have no permission to invite user.',
        StatusCode::DATA_TAG_NOT_EXIST => 'Sorry, current data tag not exist.',
        StatusCode::DATA_PROCESSING_TIPS => 'We are trying hard to process on it',
        StatusCode::DATA_NO_COLLECT_TIPS => 'Influencer not in library yet, will be collect soon',
        StatusCode::USER_INVITE_CODE_NOT_EXIST => 'Invite code not exist.',
        StatusCode::USER_INVITE_CODE_INVALID => 'Invite code invalid, please check.',
        StatusCode::USER_INVITE_CODE_USED => 'Invite code already been used.',
        StatusCode::EMAIL_ALREADY_VERIFIED => 'Email already verified， cannot be modified for now.',
        StatusCode::RESOURCE_NOT_FOUND => 'Sorry, the specified resource not found.',
        StatusCode::EMAIL_ALREADY_EXISTS => 'Sorry, the email already exists.',
        StatusCode::EMAIL_VERIFY_FAILED => 'Email verify failed.',
        StatusCode::MEMBER_PERMISSION_DENIED => 'Your subscription plan has limited access to this feature, to enjoy the full power of EchoTik, please upgrade the subscription plan.',
    ],
];

