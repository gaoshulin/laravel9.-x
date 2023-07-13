<?php

namespace App\Support;

use App\Constants\CacheConstant;
use Illuminate\Support\Facades\Cache;

/**
 * 工具方法类
 *
 * Class Utils
 * @package App\Support
 */
class Utils
{
    /**
     * 是否开启调试模式
     *
     * @return boolean
     */
    public static function isDebug()
    {
        return config('app.debug') == true;
    }

    /**
     * 缓存请求过程中执行的 SQL
     *
     * @param string  $sql
     * @param integer $time
     * @return boolean
     */
    public static function cacheRequestSql($sql, $time = 0)
    {
        if (app()->runningInConsole()) {
            return false;
        }

        $fingerprint = request()->fingerprint();
        $cacheKey = CacheConstant::DEBUG_SQL . $fingerprint;

        $content = [
            'date' => date('Y-m-d H:i:s'),
            'sql' => $sql,
            'time' => $time
        ];

        $sqls = Cache::store('array')->get($cacheKey);
        $sqls[] = $content;
        Cache::store('array')->set($cacheKey, $sqls, 300);

        return true;
    }

    /**
     * 格式原始 SQL
     */
    public static function formatRawSql($query)
    {
        $sql = vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
            return is_numeric($binding) && !is_string($binding) ? $binding : "'{$binding}'";
        })->toArray());
        // 函数处理
        foreach (['count', 'sum'] as $keyword) {
            preg_match_all("/`$keyword\(.+?\)`/", $sql, $matches);
            foreach ($matches[0] as $match) {
                $sql = str_replace($match, trim($match, '`'), $sql);
            }
        }
        return $sql;
    }

    /**
     * 简化的数字 转成 number
     */
    public static function toNumber(string $formatNum, $thouSeparator = ',')
    {
        if (preg_match('/^\d*k*$/i', $formatNum)) {
            $formatNum = substr($formatNum, 0, strlen($formatNum) - 1);
            return ((int)str_replace($thouSeparator, '', $formatNum)) * 1000;
        } elseif (preg_match('/^\d*m*$/i', $formatNum)) {
            $formatNum = substr($formatNum, 0, strlen($formatNum) - 1);
            return ((int)str_replace($thouSeparator, '', $formatNum)) * 1000 * 1000;
        } elseif (preg_match('/^\d*b*$/i', $formatNum)) {
            $formatNum = substr($formatNum, 0, strlen($formatNum) - 1);
            return ((int)str_replace($thouSeparator, '', $formatNum)) * 1000 * 1000 * 1000;
        } else {
            return $formatNum;
        }
    }

    /**
     * 范围数值转换
     *
     * @param string $format
     * @return array|int[]|null[]
     */
    public static function toRangeNum(string $format)
    {
        $ranges = ['min' => null, 'max' => null];

        if (preg_match('/\d+-\d+/i', $format)) {
            $nums = explode('-', $format);
            return ['min' => (int) $nums[0], 'max' => (int) $nums[1]];
        } elseif (preg_match('/<\d+/i', $format)) {
            return ['min' => null, 'max' => (int) substr($format, 1)];
        } elseif (preg_match('/>\d+/i', $format)) {
            return ['min' => (int) substr($format, 1), 'max' => null];
        }

        return $ranges;
    }

    /**
     * 价格范围转换
     *
     * @param string $format
     * @return array|null[]
     */
    public static function toPriceRangeNum(string $format)
    {
        $ranges = ['min' => null, 'max' => null];

        if (preg_match('/\d+-\d+/i', $format)) {
            $nums = explode('-', $format);
            return ['min' => $nums[0], 'max' => $nums[1]];
        } elseif (preg_match('/<\d+/i', $format)) {
            return ['min' => null, 'max' => substr($format, 1)];
        } elseif (preg_match('/>\d+/i', $format)) {
            return ['min' => substr($format, 1), 'max' => null];
        }

        return $ranges;
    }

    /**
     * 获取当前语言
     *
     * @return string
     */
    public static function getCurrentLang()
    {
        return Cache::store('array')->get(CacheConstant::CURRENCY_LANG) ?: 'en-US';
    }

    /**
     * 生成订单号
     */
    public static function orderNo()
    {
        return date('YmdHis').mt_rand(1000, 9999);
    }

}
