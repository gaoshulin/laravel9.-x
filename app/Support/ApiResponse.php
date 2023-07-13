<?php

namespace App\Support;

use App\Constants\ResponseConstant;
use Illuminate\Support\Facades\Response;
use App\Exceptions\BizException;
use Illuminate\Http\Response as HttpResponse;
use App\Enums\StatusCode;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{
    /**
     * 正常返回
     * @param  [type]     $data     [description]
     * @param  string     $msg      [description]
     * @param  [type]     $httpCode [description]
     * @return [type]               [description]
     */
    public function success($data = [], $msg = "ok", $httpCode = HttpResponse::HTTP_OK)
    {
        $format = ResponseConstant::$format;
        $format['code'] = StatusCode::OK;
        $format['msg'] = $msg;

        // Collection 对象，自带分页信息，直接返回响应
        if ($data instanceof ResourceCollection) {
            return $data->additional($format);
        }

        // 处理普通分页
        if ($data instanceof LengthAwarePaginator) {
            $dataArr = $data->toArray();
            $data = $dataArr['data'];
            $format['meta'] = [
                'current_page' => (int) $dataArr['current_page'],
                'from'         => (int) $dataArr['from'],
                'last_page'    => (int) $dataArr['last_page'],
                'path'         => $dataArr['path'],
                'per_page'     => (int) $dataArr['per_page'],
                'to'           => (int) $dataArr['to'],
                'total'        => (int) $dataArr['total'],
            ];
        }

        return Response::json(array_merge($format, ['data' => $data ?? []]), $httpCode);
    }

    /**
     * 业务异常
     * @param  string     $msg        [description]
     * @param  integer    $statusCode [description]
     * @param  [type]     $httpCode   [description]
     * @param  array      $errors     [description]
     * @param  array      $header     [description]
     * @return [type]                 [description]
     */
    public function fail($msg = "Oops, we've got a problem.", $statusCode = 0, $httpCode = HttpResponse::HTTP_OK, $errors = [])
    {
        $metadata = ['code' => $statusCode, 'msg' => $msg];
        $errors = $errors ? : ['messages' => $msg];
        return Response::json(array_merge($metadata, ['errors' => $errors]), $httpCode);
    }

    public function errNotFound($errors = [], $msg = 'Not Found', $statusCode = HttpResponse::HTTP_NOT_FOUND)
    {
        return $this->fail($msg, $statusCode, HttpResponse::HTTP_NOT_FOUND, $errors);
    }

    public function errBadRequest($errors = [], $msg = 'Bad Request', $statusCode = HttpResponse::HTTP_BAD_REQUEST)
    {
        return $this->fail($msg, $statusCode, HttpResponse::HTTP_BAD_REQUEST, $errors);
    }

    public function errUnauthorized($errors = [], $msg = 'Unauthorized', $statusCode = HttpResponse::HTTP_UNAUTHORIZED)
    {
        return $this->fail($msg, $statusCode, HttpResponse::HTTP_UNAUTHORIZED, $errors);
    }

    public function errInternal($errors = [], $msg = "Internal Error", $statusCode = HttpResponse::HTTP_INTERNAL_SERVER_ERROR)
    {
        return $this->fail($msg, $statusCode, HttpResponse::HTTP_INTERNAL_SERVER_ERROR, $errors);
    }

    /**
     * 业务异常返回
     * @param array $code
     * @param string $info
     */
    public function throwBizException($code= HttpResponse::HTTP_INTERNAL_SERVER_ERROR, string $info = '')
    {
        throw new BizException($code, $info);
    }

    /**
     * 返回原始响应
     * @param  [type]     $content    [description]
     * @param  array      $data       [description]
     * @param  [type]     $statusCode [description]
     * @return [type]                 [description]
     */
    public function rawResponse($content, $data = [], $statusCode = HttpResponse::HTTP_OK)
    {
        return response($content)->header('X-Content-Type', 'raw');
    }
}
