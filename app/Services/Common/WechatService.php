<?php

namespace App\Services\Common;

use App\Enums\StatusCode;
use App\Exceptions\BizException;
use App\Services\Service;
use App\Services\User\UserService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use EasyWeChat\OfficialAccount\Application;

class WechatService extends Service
{
    // 缓存前缀
    const CACHE_SCAN_LOGIN = 'scan_login_';
    const CACHE_QRCODE_URL = 'qrcode_url_';

    // Qrcode 过期
    const QRCODE_EXPIRE = 86400;

    // 接口路径
    const API_PATH_CREATE_QRCODE = '/cgi-bin/qrcode/create';
    const API_PATH_SHOW_QRCODE = 'https://mp.weixin.qq.com/cgi-bin/showqrcode';
    const API_PATH_USER_INFO = '/cgi-bin/user/info';
    const API_PATH_GET_MENU = '/cgi-bin/menu/get'; // 获取微信菜单
    const API_PATH_GET_MATERIAL = '/cgi-bin/material/batchget_material'; // 获取永久素材
    const API_PATH_CREATE_MENU = '/cgi-bin/menu/create'; // 创建微信菜单

    // 关注公众号状态
    const SUBSCRIBE_YES = 1;
    const SUBSCRIBE_NO = 0;

    protected $openid;

    protected $app;

    public function __construct(UserService $userService)
    {
        $config = config('easywechat.official_account');
        $this->app = new Application($config);

        $this->userService = $userService;
    }

    /**
     * 生成 Session 标识
     */
    public function generateSessionFlag(array $params = [])
    {
        return md5($params['ip'] . '_' . Str::uuid());
    }

    /**
     * 获取 Qrcode 地址
     */
    public function qrcode($sessionFlag)
    {
        $api = $this->app->getClient();

        // 缓存微信带参二维码
        if (!($url = Cache::get(self::CACHE_QRCODE_URL . $sessionFlag))) {
            $qrcode = $api->postJson(self::API_PATH_CREATE_QRCODE, [
                // 二维码有效期
                "expire_seconds" => self::QRCODE_EXPIRE,
                "action_name" => "QR_STR_SCENE",
                "action_info" => [
                    "scene" => ["scene_str" => $sessionFlag]
                ]
            ]);

            Log::info('QRCode response: ', $qrcode->toArray());

            $url = self::API_PATH_SHOW_QRCODE . '?' . http_build_query(['ticket' => $qrcode['ticket']]);

            Cache::put(self::CACHE_QRCODE_URL . $sessionFlag, $url, now()->addDay());
        }

        // 自定义参数返回给前端，前端轮询
        return ['url' => $url, 'session_flag' => $sessionFlag];
    }

    /**
     * 接收微信推送
     */
    public function serve()
    {
        $server = $this->app->getServer();

        $server->with(function ($message) {
            if (!$message) {
                return false;
            }

            Log::info('Message: ', [$message]);

            $method = 'handle' . ucfirst(strtolower($message['MsgType']));

            if (method_exists($this, $method)) {
                $this->openid = $message['FromUserName'];
                return call_user_func_array([$this, $method], [$message]);
            }

            Log::warning('Method not supported: ' . $method);
        });

        return $server->serve();
    }

    /**
     * 微信网页授权
     *
     * @param $user
     * @param $code
     */
    public function getUserinfo($code)
    {
        try {
            $oauth = $this->app->getOauth();
            $wxUser = $oauth->userFromCode($code);
            $wechatUser = $wxUser->toArray();

            return $wechatUser;
        } catch (\Throwable $th) {
            throw new BizException("Sorry, get wechat userinfo failed.", StatusCode::REQUEST_FAILED);
        }
    }

    /**
     * 获取微信菜单
     *
     * @return mixed
     */
    public function getMenu()
    {
        $api = $this->app->getClient();

        $response = $api->get(self::API_PATH_GET_MENU);
        if ($response->getStatusCode() != 200) {
            throw new BizException('get wechat menu failed.', $response->getStatusCode());
        }

        $response = json_decode($response->getContent(), true);
        return $response;
    }

    /**
     * 自定义创建菜单
     *
     * @param $params
     */
    public function createMenu($params)
    {
        // 自定义菜单内容
        $menuData = [
            'button' => [
                [
                    "name" => "注册",
                    "sub_button" => [
                        [
                            "type" => "view",
                            "name" => "网站",
                            "url" => "https://echotik.live/",
                        ],
                        [
                            "type" => "view",
                            "name" => "Chrome插件",
                            "url" => "https://chrome.google.com/webstore/detail/tiktok-analytics-by-echot/napmaafilgfpfohgpjokahilhgmkkfjo",
                        ]
                    ]
                ],
                [
                    "name" => "运营干货",
                    "sub_button" => [
                        [
                            "type" => "view",
                            "name" => "知识图谱",
                            "url" => "https://jj0qndrox8.feishu.cn/docx/Pzaxd34hMoUwErx5Dy1cciRMnjc",
                        ],
                        [
                            "type" => "click",
                            "name" => "数据报告",
                            "key" => "V1001_WAIT_FOR",
                        ]
                    ]
                ],
                [
                    "name" => "联系我们",
                    "sub_button" => [
                        [
                            "type" => "media_id",
                            "name" => "使用反馈",
                            "media_id" => "DtOk0XpBmJvVH3lQ5ilP9JQYJG7VWqLkaCdVv4SUmmqfpjMXmuHRLTEsENeFYK9A",
                        ],
                        [
                            "type" => "media_id",
                            "name" => "商务合作",
                            "media_id" => "DtOk0XpBmJvVH3lQ5ilP9GjwBE_rWw1EDDlfUwu4AmSXNzqTXb1Fn6t7iUh5bc9z",
                        ],
                        [
                            "type" => "media_id",
                            "name" => "加入社群",
                            "media_id" => "DtOk0XpBmJvVH3lQ5ilP9GRO_RUjR5FYZFOTXnRGLPc7NY3mXB0rlpgsLp8ehoT6",
                        ]
                    ]
                ],
            ]
        ];

        $api = $this->app->getClient();
        $response = $api->postJson(self::API_PATH_CREATE_MENU, $menuData);
        if ($response->getStatusCode() != 200) {
            throw new BizException('create wechat menu failed.', $response->getStatusCode());
        }

        $response = json_decode($response->getContent(), true);
        return $response;
    }

    /**
     * 获取微信素材
     */
    public function getMaterial()
    {
        $api = $this->app->getClient();
        $response = $api->postJson(self::API_PATH_GET_MATERIAL, [
            'type'   => 'image', // 素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
            'offset' => 0, // 从全部素材的该偏移位置开始返回，0表示从第一个素材 返回
            'count'  => 20, // 返回素材的数量，取值在1到20之间
        ]);
        if ($response->getStatusCode() != 200) {
            throw new BizException('get material failed.', $response->getStatusCode());
        }

        $response = json_decode($response->getContent(), true);
        return $response;
    }

    /**
     * 处理公众号事件
     */
    public function handleEvent($event)
    {
        $method = 'event' . ucfirst(strtolower($event['Event']));

        Log::info('Event method: ' . $method, [$event]);

        if (method_exists($this, $method)) {
            DB::beginTransaction();
            try {
                $ret = call_user_func_array([$this, $method], [$event]);
                DB::commit();
                return $ret;
            } catch (\Throwable $th) {
                DB::rollback();
                $this->isDebug() ? throw $th : Log::error('Handle event error: ' . $th->getMessage());
                return false;
            }
        }

        Log::warning('Event method not supported: ' . $method);
    }

    /**
     * 扫描带参二维码事件
     *
     * @param $event
     */
    public function eventScan($event)
    {
        Log::info('Scan：', [$event]);
        $this->handleUserLogin($event);
        return '欢迎回到 EchoTik';
    }

    /**
     * 公众号关注事件
     */
    protected function eventSubscribe($event)
    {
        Log::info('Subscribe', [$event]);
        $this->handleUserLogin($event);
        return '你好，欢迎关注！
EchoTik（<a href="https://echotik.live">echotik.live</a>）是铱氪科技公司旗下的第三方海外短视频及直播电商数据分析平台，
由 EchoSell 团队于 2022 年下半年开始研发。EchoTik 的使命就是让全球卖家、创作者、MCN和品牌都能够掌握短视频和直播间的营销方法，轻松进行产品销售和推广。';
    }

    /**
     * 取消订阅
     *
     * @param $event
     */
    protected function eventUnsubscribe($event)
    {
        Log::info('Unsubscribe：', [$event]);

    }

    /**
     * 菜单点击事件
     *
     * @param $event
     */
    public function eventClick($event)
    {
        Log::info('Unsubscribe：', [$event]);

        if (empty($event['EventKey'])) {
            return false;
        }
        $eventKey = $event['EventKey'];
        switch ($eventKey) {
            case 'V1001_WAIT_FOR' :
                $content = '敬请期待！';
                break;
            default:
                $content = '';
                break;
        }
        return $content;
    }

    /**
     * 回复文本消息
     * @param $message
     */
    public function handleText($message)
    {
        switch ($message->Content) {
            case '干货':
                $content = '🎁点击查收：TikTok超全免费知识图谱，内容涵盖TT电商运营、直播、短视频、运营工具等等，我们会不断更新内容，记得点赞&收藏哦：
<a href="https://jj0qndrox8.feishu.cn/docx/Pzaxd34hMoUwErx5Dy1cciRMnjc">不断更新ing｜TikTok超全免费知识图谱</a>';
                break;
            default:
                // $content = '抱歉，找不到您要的内容。如果您需要客户支持，可以点击EchoTik官网右下角的客服图标联系我们。';

                // 调用图文消息
                $articles = [
                    [
                        'Title' => '匹配不到内容',
                        'Description' => '如果您需要客户支持，可以通过在线客服更快的联系到我们',
                        'Url' => 'https://help.echotik.live/zh-CN/articles/6857884-%E5%A6%82%E4%BD%95%E6%9B%B4%E5%BF%AB%E5%9C%B0%E8%81%94%E7%B3%BBechotik%E5%9B%A2%E9%98%9F',
                        'PicUrl' => 'https://mmbiz.qpic.cn/mmbiz_png/WKEicfcFia8rdBsCw1fModzYgMDUb5oGjNo6Du5EUkUV73JNdfMQ89INX8VkLiadyzMenMRRpyI5epfazgAicZkxlQ/0?wx_fmt=png'
                    ]
                ];
                $content = $this->handleNews($articles);
                break;
        }

        return $content;
    }

    /**
     * 回复图片消息
     *
     * @param $mediaId
     * @return array
     */
    public function handleImage($mediaId)
    {
        return [
            'MsgType' => 'image',
            'Image' => [
                'MediaId' => $mediaId,
            ],
        ];
    }

    /**
     * 回复图文消息
     * @param array $articles
     */
    public function handleNews($articles = [])
    {
        return [
            'MsgType' => 'news',
            'ArticleCount' => count($articles),
            'Articles' => $articles
        ];
    }

    /**
     * 处理用户登录
     */
    public function handleUserLogin($event)
    {
        $openId = $this->openid;

        $data = [
            'identifer' => $openId,
            'user_id' => '',
            'social_user' => [],
        ];

        // 查询用户的消息
        $wechatAccount = [];
        if ($wechatAccount) {
            // 标记前端可登陆
            $data['user_id'] = $wechatAccount->user_id;
            return $this->markUserLogin($event, $data);
        }

        // 微信用户信息
        $wechatUser = $this->app->getClient()->get(self::API_PATH_USER_INFO, [
            'openid' => $openId,
        ]);

        Log::info('Wechat User Info：', $wechatUser->toArray());

        if ($wechatUser['subscribe'] != self::SUBSCRIBE_YES) {
            return false;
        }

        Log::info('User register successful OpenID: ' . $openId);

        $data['social_user'] = $wechatUser->toArray();

        return $this->markUserLogin($event, $data);
    }

    /**
     * 标记用户可登录
     */
    public function markUserLogin($event, $data)
    {
        if (empty($event['EventKey'])) {
            return false;
        }

        $eventKey = $event['EventKey'];

        // 关注事件的场景值会带一个前缀需要去掉
        if ($event['Event'] == 'subscribe') {
            $eventKey = Str::after($event['EventKey'], 'qrscene_');
        }

        Log::info('EventKey: ' . $eventKey, [$event['EventKey']]);

        // 标记前端可登陆
        Cache::put(self::CACHE_SCAN_LOGIN . $eventKey, $data, now()->addMinute(30));
    }

    /**
     * 获取微信接口缓存数据
     */
    public function getCacheData(array $params = [])
    {
        // 根据第三方标识在缓存中获取需要登录的用户信息
        return Cache::get(self::CACHE_SCAN_LOGIN . ($params['session_flag'] ?? ''));
    }

    /**
     * 用户登录完成，清除相关缓存
     */
    public function clearCacheData($params)
    {
        $flag = $params['session_flag'] ?? '';
        return Cache::forget(self::CACHE_SCAN_LOGIN . $flag) && Cache::forget(self::CACHE_QRCODE_URL . $flag);
    }

    public function testEvent()
    {
        $str = '{"ToUserName":"gh_6d4a62ba99ca","FromUserName":"ojVCd5yITzpiIYjLNKLEdYOySRFs","CreateTime":"1664259290","MsgType":"event","Event":"SCAN","EventKey":"ecb41635bfc3fe57e6e352b7992873f2","Ticket":"gQEX8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyQ256cm9DZGdldEQxZG5BUDF6Y2kAAgTXkjJjAwSAUQEA"}';
        // $str = '{"ToUserName":"gh_6d4a62ba99ca","FromUserName":"ojVCd5yITzpiIYjLNKLEdYOySRFs","CreateTime":"1664452018","MsgType":"event","Event":"subscribe","EventKey":"qrscene_f605dfddd612730084b1903666c4b0a0","Ticket":"gQHH7zwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWnA1T3BEZGdldEQxZXFsU2h6Y2UAAgQahDVjAwSAUQEA"}';
        // $str = '{"ToUserName":"gh_6d4a62ba99ca","FromUserName":"ojVCd5yITzpiIYjLNKLEdYOySRFs","CreateTime":"1664259429","MsgType":"event","Event":"unsubscribe","EventKey":""}';
        $event = json_decode($str, true);
        $this->openid = $event['FromUserName'];
        $this->handleEvent($event);
        die();
    }

    /**
     * 获取 JS-SDK 配置
     */
    public function getJsSdkConfig(array $params)
    {
        $utils = $this->app->getUtils();
//      buildJsSdkConfig(
//            string $url,
//            array $jsApiList = [],
//            array $openTagList = [],
//            bool $debug = false
//        )
        $config = $utils->buildJsSdkConfig(
            $params['url'] ?? '',
            $params['jsApiList'] ?? [],
            $params['openTagList'] ?? [],
            $params['debug'] ?? false,
        );

        return $config;
    }

}
