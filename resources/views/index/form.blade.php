<form class="form-horizontal" method="post" action="{{ url('index/store') }}">

    {{ csrf_field() }}

    <div class="form-group">
        <label for="name" class="col-sm-2 control-label">姓名</label>

        <div class="col-sm-5">
            <input type="text" name="name" class="form-control" id="name" placeholder="请输入姓名"
                   value="{{ old('name') ? old('name') : $student->name }}">
        </div>
        <div class="col-sm-5">
            <p class="form-control-static text-danger">{{ $errors->first('Student.name') }}</p>
        </div>
    </div>
    <div class="form-group">
        <label for="name" class="col-sm-2 control-label">电话</label>

        <div class="col-sm-5">
            <input type="text" name="mobile" class="form-control" id="mobile" placeholder="请输入电话"
                   value="{{ old('mobile') ? old('mobile') : $student->mobile }}">
        </div>
        <div class="col-sm-5">
            <p class="form-control-static text-danger">{{ $errors->first('Student.mobile') }}</p>
        </div>
    </div>
    <div class="form-group">
        <label for="age" class="col-sm-2 control-label">年龄</label>

        <div class="col-sm-5">
            <input type="text" name="age" class="form-control" id="age" placeholder="请输入年龄"
                   value="{{ old('age') ?  old('age') : $student->age }}">
        </div>
        <div class="col-sm-5">
            <p class="form-control-static text-danger">{{ $errors->first('Student.age') }}</p>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">性别</label>

        <div class="col-sm-5">
            @foreach($student->sexAttr() as $ind => $val)
                <label class="radio-inline">
                    <input type="radio" name="sex" {{ isset($student->sex) && $student->sex == $ind ? 'checked' : ''  }} value="{{ $ind }}"> {{ $val }}
                </label>
            @endforeach
        </div>
        <div class="col-sm-5">
            <p class="form-control-static text-danger">{{ $errors->first('Student.sex') }}</p>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <input type="hidden" name="id" value="{{ isset($student->id) ? $student->id : '' }}">
            <button type="submit" class="btn btn-primary">提交</button>
        </div>
    </div>
</form>
