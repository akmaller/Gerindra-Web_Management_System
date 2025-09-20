<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if(!empty($settings?->favicon_path))
        <link rel="icon" type="image/png" href="{{ asset('storage/'.$settings->favicon_path) }}">
    @endif


    {{-- SEO Tools --}}
    {!! \Artesaos\SEOTools\Facades\SEOTools::generate() !!}

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>[x-cloak]{display:none !important}</style>
    @stack('head')
</head>
<body class="bg-neutral-50 text-neutral-900">
    @include('partials.header')
    @include('partials.category-strip', ['cats' => $catStrip ?? collect()])

    <main class="min-h-screen">
        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>
