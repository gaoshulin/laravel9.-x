<?php

namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        $title = 'hello world!';

        $list = [
            [
                'id' => 1,
                'name' => 'galen',
                'date' => '20201010'
            ],
            [
                'id' => 2,
                'name' => 'a;nn',
                'date' => '20221111'
            ]
        ];

        return view('index/index', [
            'title' => $title,
            'bool' => false,
            'data' => $list
        ]);
    }

    public function show(Request $request, $id)
    {
        var_dump($request->all());
    }
}
