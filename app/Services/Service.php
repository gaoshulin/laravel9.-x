<?php

namespace App\Services;

use App\Support\Utils;

class Service
{
    protected $debug = false;

    public function isDebug()
    {
        return Utils::isDebug();
    }

    public function user()
    {
        return auth('sanctum')->user();
    }
}
