<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <base href="">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/favicon.ico') }}" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    @php
        $userId = auth()->id();
        $sessionKey = $userId ? "user_{$userId}_settings" : 'guest_settings';
        $settings = session($sessionKey, ['locale' => config('app.locale'), 'theme' => 'light']);
        $themeMode = $settings['theme'] ?? 'light';
        if ($themeMode === 'system')
            $themeMode = 'light';
    @endphp

    @if($themeMode == 'dark')
        <link href="{{ asset('assets/plugins/global/plugins.dark.bundle.css') }}" rel="stylesheet" type="text/css"
            id="kt_plugins_bundle" />
        <link href="{{ asset('assets/css/style.dark.bundle.css') }}" rel="stylesheet" type="text/css"
            id="kt_style_bundle" />
    @else
        <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css"
            id="kt_plugins_bundle" />
        <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" id="kt_style_bundle" />
    @endif
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<!--begin::Body-->

<body id="kt_body" class="bg-body">
    <!--begin::Main-->
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root">
        @yield('content')
    </div>
    <!--end::Root-->
    <!--end::Main-->
    <!--begin::Javascript-->
    <script>var hostUrl = "assets/";</script>
    <!--begin::Global Javascript Bundle(used by all pages)-->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <!--end::Global Javascript Bundle-->
    @stack('scripts')
    <!--end::Javascript-->
</body>
<!--end::Body-->

</html>