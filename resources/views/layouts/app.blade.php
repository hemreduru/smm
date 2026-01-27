<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SMM Panel') }}</title>

    <!-- Vite Assets -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body class="c-app">
    <!-- Sidebar -->
    <div class="sidebar sidebar-dark sidebar-fixed" id="sidebar">
        <div class="sidebar-brand d-none d-md-flex">
            <svg class="sidebar-brand-full" width="118" height="46" alt="CoreUI Logo">
                <use xlink:href="{{ asset('assets/brand/coreui.svg#full') }}"></use>
            </svg>
            <svg class="sidebar-brand-narrow" width="46" height="46" alt="CoreUI Logo">
                <use xlink:href="{{ asset('assets/brand/coreui.svg#signet') }}"></use>
            </svg>
        </div>
        <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="nav-icon cil-speedometer"></i> Dashboard
                </a>
            </li>
            <!-- Add more menu items here -->
        </ul>
        <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
    </div>

    <!-- Main Content Wrapper -->
    <div class="wrapper d-flex flex-column min-vh-100 bg-light">
        <!-- Header -->
        <header class="header header-sticky mb-4">
            <div class="container-fluid">
                <button class="header-toggler px-md-0 me-md-3" type="button"
                    onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()">
                    <i class="icon icon-lg cil-menu"></i>
                </button>
                <a class="header-brand d-md-none" href="#">
                    <svg width="118" height="46" alt="CoreUI Logo">
                        <use xlink:href="{{ asset('assets/brand/coreui.svg#full') }}"></use>
                    </svg>
                </a>
                <ul class="header-nav d-none d-md-flex">
                    <li class="nav-item"><a class="nav-link" href="#">Dashboard</a></li>
                </ul>
                <ul class="header-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#">
                            <i class="icon icon-lg cil-bell"></i>
                        </a></li>
                </ul>
            </div>
        </header>

        <!-- Main Content -->
        <div class="body flex-grow-1 px-3">
            <div class="container-lg">
                @// Flash Messages via Session for JS handling
                @if(session('success'))
                    <script>window.flashSuccess = "{{ session('success') }}";</script>
                @endif
                @if(session('error'))
                    <script>window.flashError = "{{ session('error') }}";</script>
                @endif
                @if(session('warning'))
                    <script>window.flashWarning = "{{ session('warning') }}";</script>
                @endif
                @if(session('info'))
                    <script>window.flashInfo = "{{ session('info') }}";</script>
                @endif

                @yield('content')
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div><a href="https://coreui.io">CoreUI</a> © 2024 creativeLabs.</div>
            <div class="ms-auto">Powered by&nbsp;<a href="https://coreui.io/">CoreUI</a></div>
        </footer>
    </div>
</body>

</html>