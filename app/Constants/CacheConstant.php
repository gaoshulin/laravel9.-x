<?php

namespace App\Constants;

/**
 * 缓存 Key 常量
 */
class CacheConstant
{
    // 缓存页大小
    const PAGE_MAX_SIZE = 100;
    const DEBUG_SQL = 'debug.sql.';

    // 用户
    const USER_TOKEN_INFO = 'user:token:';
    const USER_TOKEN_TOKENABLE = 'user:token:tokenable:';
    const USER_TOKEN_LAST_USED = 'user:token:lastused:';

    // 语言
    const CURRENCY_LANG = 'lang:current';
    // 货币汇率
    const CURRENCY_CURRENT = 'currency:current';

    // 缓存时间
    const HOUR = 3600;
    const HALF_DAY = 43200;
    const DAY = 86400;
    const WEEK = 604800;
}
