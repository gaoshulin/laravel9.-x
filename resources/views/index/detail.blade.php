@extends('common.layouts')

@section('content')

    <div class="panel panel-default">
        <div class="panel-heading">学生详情</div>

        <table class="table table-bordered table-striped table-hover ">
            <tbody>
            <tr>
                <td>ID</td>
                <td>{{ $student->id }}</td>
            </tr>
            <tr>
                <td>姓名</td>
                <td>{{ $student->name }}</td>
            </tr>
            <tr>
                <td>电话</td>
                <td>{{ $student->mobile }}</td>
            </tr>
            <tr>
                <td>年龄</td>
                <td>{{ $student->age }}</td>
            </tr>
            <tr>
                <td>性别</td>
                <td>{{ $student->sexAttr($student->sex) }}</td>
            </tr>
            <tr>
                <td>添加时间</td>
                <td>{{ $student->create_time }}</td>
            </tr>
            <tr>
                <td>更新时间</td>
                <td>{{ $student->update_time }}</td>
            </tr>
            </tbody>
        </table>
    </div>
@stop
