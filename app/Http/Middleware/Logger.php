<?php

namespace App\Http\Middleware;

use App\Enums\StatusCode;
use Closure;
use Illuminate\Support\Facades\Log;

class Logger
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
        // 请求时间
        $reqTime = now();

        // 处理请求
        $response = $next($request);

        // 响应时间
        $respTime = now();

        // 请求耗时 ms
        $elapsedTime = $respTime->getPreciseTimestamp(3) - $reqTime->getPreciseTimestamp(3);

        // 请求日志
        $log = $response->getOriginalContent();

        // 处理原始请求日志格式
        if ($response->headers->get('X-Content-Type') == 'raw') {
            $this->log($request, method_exists($response, 'getContent') ? $response->getContent() : [], $elapsedTime, $response->status());
            return $response;
        }

        // 写日志
        $this->log($request, $log, $elapsedTime, $response->status());

        return $response;
    }

    /**
     * 记录请求到日志
     *
     * @param object $request 请求对象
     * @param array $data 响应体
     * @param int $reqTime 请求耗时
     * @return void
     */
    public function log($request, $data, int $reqTime, int $httpCode)
    {
        $bizCode = $data['code'] ?? StatusCode::OK;
        $logStr =self::parseLog($request, $data, $reqTime, $httpCode, $bizCode);
        Log::channel('request')->debug($logStr);
    }

    public static function parseLog($request, $data, int $reqTime, int $httpCode, $bizCode)
    {
        // 记录请求参数和响应
        $log = [
            'header'   => $request->header(),
            'body'     => $request->all() ?: $request->getContent(),
            'response' => $data,
        ];
        $logStr = json_encode($log, JSON_UNESCAPED_UNICODE);

        $fingerprint = request()->fingerprint();

        return sprintf("%s|%s|%s|%s|%s|%s|%s|%s|%s", $fingerprint, $request->getClientIp(), $reqTime, $request->method(), $httpCode, $bizCode, $request->path(), $request->fullUrl(), $logStr);
    }
}
