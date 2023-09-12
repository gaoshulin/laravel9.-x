@extends('common.layouts')

@section('content')

    @include('common.message')

    <!-- 自定义内容区域 -->
    <div class="panel panel-default">
        <div class="panel-heading">学生列表</div>
        <table class="table table-striped table-hover table-responsive">
            <thead>
            <tr>
                <th>ID</th>
                <th>姓名</th>
                <th>电话</th>
                <th>年龄</th>
                <th>性别</th>
                <th>添加时间</th>
                <th>更新时间</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            @foreach($data as $student)
                <tr>
                    <th scope="row">{{ $student->id }}</th>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->mobile }}</td>
                    <td>{{ $student->age }}</td>
                    <td>{{ $student->sexAttr($student->sex) }}</td>
                    <td>{{ $student->create_time }}</td>
                    <td>{{ $student->update_time }}</td>
                    <td>
                        <a href="{{ url('index/show', ['id' => $student->id]) }}" class="btn btn-xs btn-info">详情</a>
                        <a href="{{ url('index/update', ['id' => $student->id]) }}" class="btn btn-xs btn-success">修改</a>
                        <a href="{{ url('index/delete', ['id' => $student->id]) }}" class="btn btn-xs btn-danger"
                           onclick="if (confirm('确定要删除吗？') == false) return false;">删除</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <!-- 分页  -->
    <div style="padding-bottom: 30px;">
        <div class="pull-right">
            {{ $data->render() }}
        </div>

    </div>
@stop
