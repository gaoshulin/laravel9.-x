<?php

namespace App\Http\Controllers\Index;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        $title = 'indexd/index';

        $list = Db::table('demo')->select(["id","title","create_time"])->get();

        $data = [
            'title' => $title,
            'data' => $list,
             'bool' => false,
        ];
        return view('index/index', $data);
    }

    public function show(Request $request, $id)
    {
        // 动态 where
        $user = DB::table('users')
            ->whereIdOrEmail($id, '1453811292@qq.com')
            // ->whereNameAndAge('galen', 25)
            ->first();

        var_dump($user);
    }
}
