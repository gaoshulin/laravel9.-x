<?php

namespace App\Enums;

use App\Enums\Enum;

/**
 * 业务状态码定义
 */
final class StatusCode extends Enum
{
    // 成功
    const OK = 1;
    // 错误
    const ERROR = 0;
    // 业务状态码-起点
    const BIZ_CODE_FROM = 100000;

    // 系统级状态码 1001-99999

    // 通用 1001-1999
    const REQUEST_PARAMS_INVALID = 1001; // 请求参数无效
    const REQUEST_FAILED = 1002; // 请求失败
    const RESOURCE_NOT_FOUND = 1404; // 请求的资源不存在

    // 用户相关 10001-29999
    const USER_UNAUTHORIZED = 10001;
    const USER_LOGIN_CREDENTIALS_ERROR = 10002; // 用户名或密码错误
    const USER_PERMISSION_DENIED = 10003; // 用户名没有权限访问
    // 邀请码无效
    const USER_INVITE_CODE_NOT_EXIST = 10004;
    const USER_INVITE_CODE_INVALID = 10005;
    const USER_INVITE_CODE_USED = 10006;

    // 邮箱未认证
    const EMAIL_NOT_VERIFY_ERROR = 20001;
    // 已认证邮箱，暂不能重复认证
    const EMAIL_ALREADY_VERIFIED = 20002;
    // 没有权限邀请新用户
    const PERMISSION_DENIED_TO_INVITE_USER = 20003;
    // 继续邮箱注册流程
    const CONTINUE_EMAIL_SIGNUP = 20004;
    const EMAIL_ALREADY_EXISTS = 20005;
    const EMAIL_VERIFY_FAILED = 20006;

    // 没有对应的会员权限
    const MEMBER_PERMISSION_DENIED = 50001;

    // 业务状态码
    // EchoTik 100001-109999
    // 数据通用 100001 - 100999
    const DATA_TAG_NOT_EXIST = 100001;
    const DATA_PROCESSING_TIPS = 100002;
    const DATA_NO_COLLECT_TIPS = 100003;
    const DATA_NEED_BE_MASKED = 100004;

    /**
     * 是否为业务异常
     * @param int $code
     * @return boolean
     */
    public static function isBizException($code)
    {
        return $code > self::BIZ_CODE_FROM;
    }
    
}
