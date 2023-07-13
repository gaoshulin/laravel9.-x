<?php

namespace App\Http\Middleware;

use App\Constants\CacheConstant;
use App\Constants\ResponseConstant;
use App\Enums\StatusCode;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;

class Response
{
    protected $debug = false;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->debug = config('app.debug', false);

        // 处理请求参数
        $request->merge($this->prepareParams($request->all()));

        // 处理请求
        $response = $next($request);

        // 请求 ID
        // 只是请求指纹，相同请求内容的 ID 会相同，查日志还得结合请求时间一起看
        $fingerprint = request()->fingerprint();
        $response->header('Request-Id', $fingerprint);

        if (is_array($response)) {
            return $response;
        }

        // 如果是导出Excel类型直接返回
        if ($response instanceof BinaryFileResponse) {
            return $response;
        }

        // 返回原始格式
        if ($response->headers->get('X-Content-Type') == 'raw') {
            return $response;
        }

        // 获取请求内容
        $oriContent = $response->getOriginalContent();
        $content = json_decode($response->getContent(), true) ?? $oriContent;
        $content = is_array($oriContent) ? $oriContent : $content;
        if (isset($content['code']) && $content['code'] != StatusCode::OK) {
            return $response;
        }

        $data = ResponseConstant::$format;
        $data['data'] = $content['data'] ?? $content;
        $data['msg'] = $content['msg'] ?? $data['msg'];

        // 处理错误信息
        if (isset($content['errors'])) {
            $data['code'] = $response->status();
            $data['errors'] = $content['errors'];
            // 处理错误码信息
            $msg = StatusCode::getDesc($data['code']);
            $data['msg'] = $msg ? : ($content['message'] ?? '');
        }

        // 处理分页
        if ($content['meta'] ?? []) {
            $data['meta'] = $content['meta'];
        }

        if ($oriContent instanceof LengthAwarePaginator) {
            $data['meta'] = [
                'current_page' => (int) $content['current_page'],
                'from'         => (int) $content['from'],
                'last_page'    => (int) $content['last_page'],
                'path'         => $content['path'],
                'per_page'     => (int) $content['per_page'],
                'to'           => (int)$content['to'],
                'total'        => (int) $content['total'],
            ];
        }

        // 处理 data 嵌套
        if ($data['data']['data'] ?? []) {
            $data['data'] = $data['data']['data'];
        }

        // debug 状态则在接口中返回 sql
        if ($this->debug) {
            // sql 日志
            $sqlKey = CacheConstant::DEBUG_SQL . $fingerprint;
            $sqls = Cache::store('array')->get($sqlKey);
            $data['debug']['sql'] = $sqls;
        }

        $temp = ($content) ? array_merge(ResponseConstant::$format, $data) : ResponseConstant::$format;
        $response = $response instanceof JsonResponse ? $response->setData($temp) : $response->setContent($temp);

        return $response;
    }

    /**
     * 预处理全局参数
     */
    public function prepareParams($params = [])
    {
        // 设置全局语言
        if ($params['lang'] ?? false) {
            $locale = str_replace('-', '_', $params['lang']);
            App::setLocale($locale);
            Cache::store('array')->set(CacheConstant::CURRENCY_LANG, $params['lang'] ?? 'en-US');
        }

        // 全局货币
        if ($params['currency'] ?? false) {
            Cache::store('array')->set(CacheConstant::CURRENCY_CURRENT, $params['currency'] ?? 'USD');
        }

        return $params;
    }
}
