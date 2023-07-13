<?php

namespace App\Services\User;

use App\Events\LoginEvent;
use App\Events\UserinfoUpdateEvent;
use App\Http\Resources\Api\User\UserResource;
use App\Repositories\User\UserRepositoryEloquent as UserRepository;
use App\Services\Service;
use App\Events\LogoutEvent;
use App\Events\SignUpEvent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserService extends Service
{
     /**
      * @var UserRepository
      */
     protected $repository;

	public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function find(array $params)
    {
        $list = $this->repository->findModels($params);
        return $list;
    }

    public function get($id)
    {
        return $this->repository->findOrFail($id);
    }

    public function getBy($field, $value)
    {
        return $this->repository->findByField($field, $value)->first();
    }

    public function create(array $params)
    {
        return $this->repository->create($params);
    }

    /**
     * 更新
     *
     * @param array $params
     * @param $id
     */
    public function update(array $params, $id)
    {
        if ($params['password'] ?? false) {
            $params['password'] = Hash::make($params['password']);
        }

        // 验证用户邮箱
        Validator::make($params, [
            'email' => [
                function ($attribute, $value, $fail) use ($params, $id){
                    $user = $this->getBy('email', $params['email']);
                    if ($user && $user['id'] != $id) {
                        $fail('Sorry, the email already exists.');
                    }
                },
            ],
        ])->validate();

        $user = $this->repository->update($params, $id);

        // 清除用户缓存
        event(new UserinfoUpdateEvent($user));

        return $user;
    }

    /**
     * 用户注册
     *
     * @param array $params
     */
    public function signup(array $params)
    {
        // 创建用户
        $user = $this->create($params);

        // 用户注册事件
        event(new SignUpEvent($user, $params));

        // 生成 token
        return [
            'access_token' => $user->createToken($user->email)->plainTextToken,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * 用户登录
     */
    public function login(array $params)
    {
        $user = $this->getBy('email', $params['email']);

        // 发送登录事件
        event(new LoginEvent($user, $params));

        return [
            'access_token' => $user->createToken($user->email)->plainTextToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ];
    }
    /**
     * 用户注销
     *
     * @param $user
     * @param array $params
     */
    public function logout($user, array $params = [])
    {
        $token = $user->currentAccessToken();

        if (method_exists($token, 'delete')) {
            $token->delete();
        } else {
            // for scantum cookie
            auth()->guard('web')->logout();
        }

        event(new LogoutEvent($user, $params));

        return true;
    }
}
