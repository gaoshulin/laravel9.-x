<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

<div class="container">
    <h3>{{ $title }}</h3>

    @if($bool)
         <p>Hello</p>
    @else
        <p>Hi</p>
    @endif

    @empty($bool)
        <p>world hello</p>
    @endempty

    @foreach ($data as $value)
        <ul>
            <li>ID: {{ $value['id'] }}</li>
            <li>Name: {{ $value['name'] }}</li>
            <li>Date: {{ $value['date'] }}</li>
        </ul>
    @endforeach
</div>

</body>
</html>
