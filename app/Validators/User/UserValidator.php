<?php

namespace App\Validators\User;

use \Prettus\Validator\Contracts\ValidatorInterface;
use \Prettus\Validator\LaravelValidator;

/**
 * Class UserValidator.
 *
 * @package namespace App\Validators\User;
 */
class UserValidator extends LaravelValidator
{
    const RULE_LOGIN = 'login';

    /**
     * Validation Rules
     *
     * @var array
     */
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'name' => ['required', 'min:2', 'max:64'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8'],
        ],
        ValidatorInterface::RULE_UPDATE => [
            'name' => ['required', 'min:2', 'max:64'],
        ],
        self::RULE_LOGIN => [
//            'name' => ['required', 'min:2', 'max:64'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ],
    ];
}
