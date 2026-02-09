<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300..600" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;800" rel="stylesheet" type="text/css">

    <!-- Styles & Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @if (request()->routeIs('admin*'))
        @vite(['resources/js/flatpickr.js', 'resources/js/tinymce.js'])
    @endif

    @livewireStyles

    <!-- Robots -->
    <meta name="robots" content="noindex" />
</head>

<body>
    <div id="app">
        @include('layouts.navbar')
        <main class="py-4">
            <div class="container">

                <ol class="breadcrumb">
                    @foreach (Breadcrumbs::current() as $crumbs)
                        @if ($crumbs->url() && !$loop->last)
                            <li class="breadcrumb-item">
                                <a href="{{ $crumbs->url() }}">{{ $crumbs->title() }}</a>
                            </li>
                        @else
                            <li class="breadcrumb-item active">{{ $crumbs->title() }}</li>
                        @endif
                    @endforeach
                </ol>

                @yield('content')
                @include('layouts.footer')
            </div>
        </main>
    </div>
    <!-- Scripts -->
    @stack('scripts')
    @livewireScripts
</body>

</html>
