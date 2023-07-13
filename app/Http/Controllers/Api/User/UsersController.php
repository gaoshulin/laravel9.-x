<?php

namespace App\Http\Controllers\Api\User;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Services\User\UserService;
use App\Http\Resources\Api\User\UserResource;
use App\Repositories\User\UserRepository;
use App\Validators\User\UserValidator;

/**
 * Class IndexController.
 *
 * @package namespace App\Http\Controllers\Api\User;
 */
class UsersController extends Controller
{
    /**
     * @var UserService
     */
    protected $service;

    /**
     * @var UserValidator
     */
    protected $validator;

    /**
     * IndexController constructor.
     *
     * @param UserRepository $repository
     * @param UserValidator $validator
     */
    public function __construct(UserService $service, UserValidator $validator)
    {
        $this->service  = $service;
        $this->validator  = $validator;
    }

    /**
     * Display a listing of the resource.
     */
    public function list(Request $request)
    {
        $users = $this->service->find($request->all());
        return $this->success(UserResource::collection($users));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = $this->service->get($id);
        return $this->success(new UserResource($user));
    }

    /**
     * 查询用户信息
     *
     * @param Request $request
     */
    public function userinfo(Request $request)
    {
        $user = $request->user();

        return $this->success(new UserResource($user));
    }

    /**
     * 更新用户信息
     *
     * @param Request $request
     */
    public function update(Request $request)
    {
        try {
            $params = $request->all();

            $this->validator->with($params)->passesOrFail(UserValidator::RULE_UPDATE);
        } catch (ValidatorException $e) {
            return $this->errBadRequest($e->getMessageBag());
        }

        $user = $request->user();

        // 更新之后的用户信息
        $user = $this->service->update($params, $user->id);

        return $this->success(new UserResource($user));
    }

    /**
     * 用户注册
     *
     * @param Request $request
     */
    public function signup(Request $request)
    {
        try {
            $params = $request->all();

            $this->validator->with($params)->passesOrFail(UserValidator::RULE_CREATE);
        } catch (ValidatorException $e) {
            return $this->errBadRequest($e->getMessageBag());
        }

        $res = $this->service->signup($params);

        return $this->success($res);
    }

    /**
     * 用户登录
     *
     * @param Request $request
     */
    public function login(Request $request)
    {
        try {
            $params = $request->all();

            $this->validator->with($params)->passesOrFail(UserValidator::RULE_LOGIN);
        } catch (ValidatorException $e) {
            return $this->errBadRequest($e->getMessageBag());
        }

        $res = $this->service->login($params);

        return $this->success($res);
    }

    /**
     * 退出登录
     *
     * @param Request $request
     */
    public function logout(Request $request)
    {
        // 当前会话 token 清除
        $ret = $this->service->logout($request->user(), ['token' => $request->bearerToken()]);

        return $this->success($ret);
    }
}
