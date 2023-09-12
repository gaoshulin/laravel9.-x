@extends('common.layouts')

@section('content')

    @include('common.validator')

    <div class="panel panel-default">
        <div class="panel-heading">新增学生</div>
        <div class="panel-body">
            @include('index.form')
        </div>
    </div>
@stop
