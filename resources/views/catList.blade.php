<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>     
    </style>
</head>
<body >

список каталогов


@include('catList0', ['cats' => $cats ])

{{-- @foreach($cats as $c)
<br/>{{ $c['name'] }}
<div style="padding-left: 20px;" >

    @foreach($c['in'] as $c2)
    <br/>{{ $c2['name'] }}
    <div style="padding-left: 20px;" >

        @foreach($c2['in'] as $c3)
        <br/>{{ $c3['name'] }}
        <div style="padding-left: 20px;" >
        
        </div>
        @endforeach    
    
        </div>
    @endforeach    

</div>
@endforeach

<pre>{{ print_r($cats) }}</pre> --}}

</body>

</html>
