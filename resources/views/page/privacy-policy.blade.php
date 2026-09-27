<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ language_direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $data->name }}</title>
    <style>body{font-family:system-ui,sans-serif;line-height:1.6;margin:0;color:#172033;background:#f6f8fb}main{max-width:900px;margin:32px auto;padding:0 20px}article{background:#fff;padding:28px;border-radius:12px}</style>
</head>
<body><main><article><h1>{{ $data->name }}</h1>{!! $data->description !!}</article></main></body>
</html>
{{--

@section('title')
{{ __($module_action) }} {{ __($module_title) }}
@endsection


@push('after-styles')
<link rel="stylesheet" href="{{ mix('modules/constant/style.css') }}">
@endpush
@section('content')
    <div class="container">
        
        <div class="card">        
            <div class="card-body">
                
            {!! $data->description !!}

            </div>
        </div>
    </div>

@endsection
--}}
