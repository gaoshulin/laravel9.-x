<?php

namespace App\Http\Controllers\Api\Data;

use App\Exports\DemoListExport;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Prettus\Validator\Contracts\ValidatorInterface;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Services\Data\DemoService;
use App\Http\Resources\Api\Data\DemoResource;
use App\Repositories\Data\DemoRepository;
use App\Validators\Data\DemoValidator;

/**
 * Class DemosController.
 *
 * @package namespace App\Http\Controllers\Api\Data;
 */
class DemosController extends Controller
{
    /**
     * @var DemoService
     */
    protected $service;

    /**
     * @var DemoValidator
     */
    protected $validator;

    /**
     * DemosController constructor.
     *
     * @param DemoRepository $repository
     * @param DemoValidator $validator
     */
    public function __construct(DemoService $service, DemoValidator $validator)
    {
        $this->service  = $service;
        $this->validator  = $validator;
    }

    /**
     * Display a listing of the resource.
     */
    public function list(Request $request)
    {
        $demos = $this->service->find($request->all());
        return $this->success(DemoResource::collection($demos));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $demo = $this->service->get($id);
        return $this->success(new DemoResource($demo));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $params = $request->all();

            $this->validator->with($params)->passesOrFail(ValidatorInterface::RULE_CREATE);

            $demo = $this->service->create($params);

            return $this->success($demo);
        } catch (ValidatorException $e) {
            return $this->errBadRequest($e->getMessageBag());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $this->validator->with($request->all())->passesOrFail(ValidatorInterface::RULE_UPDATE);

            $demo = $this->service->update($request->all(), $id);

            return $this->success($demo);
        } catch (ValidatorException $e) {
            return $this->errBadRequest($e->getMessageBag());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->service->delete($id);
        } catch (Exception $e) {
            return $this->errInternal($e->getMessage());
        }

        return $this->success();
    }

    /**
     * 队列
     *
     * @param Request $request
     */
    public function jobs(Request $request)
    {
        $params = $request->all();

        // 调用队列处理
        $this->service->demoJobs($params);

        return $this->success();
    }

    /**
     * 导出 excel 文件
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportList(Request $request)
    {
        $params = $request->all();
        $data = $this->service->find($params);

        $fileName = 'demo list'.'-'.date('Y-m-d :i:s').".xlsx"; // 导出的文件名

        return Excel::download(new DemoListExport($data, $params), $fileName);
    }
}
