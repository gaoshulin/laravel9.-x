<?php

namespace App\Listeners;

use App\Constants\CacheConstant;
use App\Events\LoginEvent;
use App\Events\LogoutEvent;
use App\Events\SignUpEvent;
use App\Events\UserinfoUpdateEvent;
use App\Models\User\PersonalAccessToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserSubscriber
{
    /**
     * 用户注册事件
     *
     * @param SignUpEvent $event
     */
    public function userSignup(SignUpEvent $event)
    {
        $user = $event->user;
        $params = $event->params;

        // 记录日志
        $message = sprintf("User %s-%s-%s signup successful.", $user->id, $user->name, $user->email);
        Log::info($message, $params);
    }

    /**
     * 用户登录事件
     *
     * @param LoginEvent $event
     */
    public function userLogin(LoginEvent $event)
    {
        $user = $event->user;
        $params = $event->params;

        // 记录日志
        $message = sprintf("User %s-%s-%s login successful.", $user->id, $user->name, $user->email);
        Log::info($message, $params);
    }

    /**
     * 用户注销事件
     */
    public function userLogout(LogoutEvent $event)
    {
        $user = $event->user;

        $message = sprintf("User %s-%s-%s logout.", $user->id, $user->name, $user->email);
        Log::info($message);
    }

    /**
     * 退出登录清除缓存
     */
    public function clearTokenCache(LogoutEvent $event)
    {
        $user = $event->user;

        // 清除 token cache
        $token = PersonalAccessToken::parseToken($event->params['token']);
        Cache::delete(CacheConstant::USER_TOKEN_INFO . $token);

        $message = sprintf("Clear user token cache: %s-%s-%s.", $user->id, $user->name, $user->email);
        Log::info($message);

        // 清除用户信息缓存
        event(new UserinfoUpdateEvent($event->user));
    }

    /**
     * 清除用户缓存
     */
    public function clearUserinfoCache(UserinfoUpdateEvent $event)
    {
        $user = $event->user;

        // 清除用户信息缓存
        Cache::delete(CacheConstant::USER_TOKEN_TOKENABLE . $user->id);

        $message = sprintf("Clear user info cache: %s-%s-%s.", $user->id, $user->name, $user->email);
        Log::info($message);
    }


    /**
     * Handle the event.
     *
     * @param \Illuminate\Events\Dispatcher $events
     * @return void
     */
    public function subscribe($events)
    {
        // 注册事件
        $events->listen(SignUpEvent::class, [UserSubscriber::class, 'userSignup']);

        // 登录事件
        $events->listen(LoginEvent::class, [UserSubscriber::class, 'userLogin']);

        // 退出登录事件
        $events->listen(LogoutEvent::class, [UserSubscriber::class, 'userLogout']);
        $events->listen(LogoutEvent::class, [UserSubscriber::class, 'clearTokenCache']);

        // 清除用户token
        $events->listen(UserinfoUpdateEvent::class, [UserSubscriber::class, 'clearUserinfoCache']);
    }
}
