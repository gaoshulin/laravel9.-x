<?php

namespace App\Models\User;

use App\Constants\CacheConstant;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * 提取 token
     */
    public static function parseToken($token)
    {
        if (strpos($token, '|') !== false) {
            [$id, $token] = explode('|', $token, 2);
        }
        return $token;
    }

    /**
     * 查询用户 Token 并缓存
     */
    public static function findToken($tokenStr)
    {
        $token = self::parseToken($tokenStr);

        $cacheKey = CacheConstant::USER_TOKEN_INFO . $token;
        $instance = Cache::remember($cacheKey, CacheConstant::HALF_DAY, function () use ($token) {
            return parent::findToken($token) ?? null;
        });

        return $instance;
    }

    /**
     * 缓存 token 所属的 User Model
     */
    public function getTokenableAttribute()
    {
        $cacheKey = CacheConstant::USER_TOKEN_TOKENABLE . $this->tokenable_id;
        return Cache::remember($cacheKey, CacheConstant::HALF_DAY, function () {
            return parent::tokenable()->first();
        });
    }

    /**
     * 免去每次更新 last used at
     */
    public function save(array $options = [])
    {
        $cacheKey = CacheConstant::USER_TOKEN_LAST_USED . $this->id;

        $changes = $this->getDirty();
        // Check for 2 changed values because one is always the updated_at column
        if (array_key_exists('last_used_at', $changes) && count($changes) == 1) {
            if (Cache::get($cacheKey)) {
                return false;
            }
        }

        parent::save();

        Cache::put($cacheKey, true, CacheConstant::HALF_DAY);

        return true;
    }
}
