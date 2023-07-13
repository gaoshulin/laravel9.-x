<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use App\Constants\CacheConstant;

/**
 * token中间件-设置以参数形式传递的token
 */
class Token
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request['token'] ?? '';

        $request->headers->set('Authorization', 'Bearer '.$token);

        if (!$this->checkUser()) {
            throw new AuthenticationException;
        }

        // 请求头参数处理-合并到 request
        $params = $this->headerParams($request->all(), $request->header());

        // 处理请求参数
        $request->merge($this->prepareParams($params));

        $response = $next($request);

        return $response;
    }

    /**
     * 检查用户
     */
    public function checkUser()
    {
        return auth('sanctum')->check();
    }

    /**
     * 预处理全局参数
     *
     * @param array $params
     * @return array|mixed
     */
    public function prepareParams($params = [])
    {
        // 设置全局语言
        if ($params['lang'] ?? false) {
            $locale = str_replace('-', '_', $params['lang']);
            App::setLocale($locale);
        }

        // 全局货币
        if ($params['currency'] ?? false) {
            Cache::store('array')->set(CacheConstant::CURRENCY_CURRENT, $params['currency'] ?? 'USD');
        }

        return $params;
    }

    /**
     * 处理请求头参数
     *
     * @param array $params
     * @param array $headers
     * @return array
     */
    public function headerParams($params = [], $headers = [])
    {
        if (isset($headers['x-lang']) && isset($headers['x-lang'][0])) {
            $params['lang'] = $headers['x-lang'][0];
        }
        if (isset($headers['x-region']) && isset($headers['x-region'][0])) {
            $params['region'] = $headers['x-region'][0];
        }
        if (isset($headers['x-currency']) && isset($headers['x-currency'][0])) {
            $params['currency'] = $headers['x-currency'][0];
        }

        return $params;
    }

}
