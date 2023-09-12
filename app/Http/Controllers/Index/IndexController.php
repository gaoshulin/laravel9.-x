<?php

namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use App\Services\Index\IndexService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * class IndexController
 */
class IndexController extends Controller
{
    /**
     * @var string 当前路由地址
     */
    protected $pathInfo;

    /**
     * @var IndexService
     */
    protected $service;

    /**
     * @param IndexService $service
     */
    public function __construct(IndexService $service)
    {
        $this->service = $service;

        $this->pathInfo = request()->getPathInfo();

        view()->share('pathInfo', $this->pathInfo);
    }

    /**
     * 列表页面
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        $list = $this->service->find($request->all());

        return view('index/index', ['data' => $list]);
    }

    /**
     * 详情页面
     *
     * @param Request $request
     * @param $id
     */
    public function show(Request $request, $id)
    {
        $student = $this->service->get($id);

        return view('index/detail', ['student' => $student]);
    }

    /**
     * 新增页面
     *
     * @param Request $request
     */
    public function create(Request $request)
    {
        $student = $this->service->model();

        return view('index/create', ['student' => $student]);
    }

    /**
     * 更新页面
     *
     * @param Request $request
     */
    public function update(Request $request, $id)
    {
        $student = $this->service->get($id);

        return view('index/update', ['student' => $student]);
    }

    /**
     * 删除
     *
     * @param Request $request
     * @param $id
     */
    public function delete(Request $request, $id)
    {
        $result = $this->service->delete($id);
        if ($result !== false) {
            return redirect('index/index')->with('success', 'success-' . $id);
        } else {
            return redirect('index/index')->with('error', 'failed-' . $id);
        }
    }

    /**
     * 数据存储
     *
     * @param Request $request
     */
    public function store(Request $request)
    {
        $params = $request->input();

        // Validator类验证
        $validator = Validator::make($params, [
            'name' => 'required|min:2|max:10',
            'mobile' => 'required|min:7|max:15',
            'age' => 'required|integer',
            'sex' => 'required|integer',
        ], [
            'required' => ':attribute 为必填项',
            'min' => ':attribute 长度不符合要求',
            'max' => ':attribute 长度不符合要求',
            'integer' => ':attribute 必须为整数',
        ], [
            'name' => '姓名',
            'mobile' => '电话',
            'age' => '年龄',
            'sex' => '性别',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 新增 或 更新
        if ($params['id'] ?? false) {
            $result = $this->service->update($params, $params['id']);
        } else {
            $result = $this->service->create($params);
        }

        if ($result !== false) {
            return redirect('index/index')->with('success', 'store successfully.');
        } else {
            return redirect()->back()->with('error', 'store failed.');
        }
    }

}
