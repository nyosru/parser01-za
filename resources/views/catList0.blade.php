@foreach($cats as $c)
{{ $c['name'] }}
<div style="padding-left: 20px; backround-color: rgba(255,230,230,0.8);" >
    @include('catList0', ['cats' => $c['in'] ])
</div>
@endforeach