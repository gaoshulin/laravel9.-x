<?php
/**
 * Created by PhpStorm
 * USER: Administrator
 * Author: Galen
 * Date: 2023/4/27 11:07
 */

use App\Enums\StatusCode;

return [
    StatusCode::class => [
        StatusCode::ERROR => '抱歉，服务器开小差了，请稍后再试',
        StatusCode::REQUEST_FAILED => "请求失败，接口响应：:response",
        StatusCode::USER_UNAUTHORIZED => '用户尚未认证',
        StatusCode::USER_PERMISSION_DENIED => '抱歉，您没有权限访问该资源',
        StatusCode::USER_LOGIN_CREDENTIALS_ERROR => '用户名或密码错误，请检查',
        StatusCode::CONTINUE_EMAIL_SIGNUP => '请继续填写邮箱&密码完成注册流程',
        StatusCode::EMAIL_NOT_VERIFY_ERROR => '邮箱尚未认证，请检查',
        StatusCode::PERMISSION_DENIED_TO_INVITE_USER => '抱歉，您没有权限邀请新用户',
        StatusCode::DATA_TAG_NOT_EXIST => '抱歉，请求的数据主题不存在',
        StatusCode::DATA_PROCESSING_TIPS => '我们正在努力处理中，请稍后再试',
        StatusCode::DATA_NO_COLLECT_TIPS => '抱歉，未找到该达人，我们会尽快收录',
        StatusCode::USER_INVITE_CODE_NOT_EXIST => '邀请码不存在，请确认邀请码输入无误',
        StatusCode::USER_INVITE_CODE_INVALID => '无效的邀请码，请确认邀请码输入无误',
        StatusCode::USER_INVITE_CODE_USED => '邀请码已使用',
        StatusCode::EMAIL_ALREADY_VERIFIED => '用户邮箱已认证，暂不能更改邮箱',
        StatusCode::RESOURCE_NOT_FOUND => '抱歉，指定的资源不存在或无法找到',
        StatusCode::EMAIL_ALREADY_EXISTS => '抱歉，邮箱已存在',
        StatusCode::EMAIL_VERIFY_FAILED => '邮箱验证失败',
        StatusCode::MEMBER_PERMISSION_DENIED => '抱歉，您当前会员方案暂时无法使用该功能，升级至高版本会员即可解锁。',
    ],
];

