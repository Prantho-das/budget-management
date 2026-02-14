<!doctype html>
<html lang="en">
<head>
        
        <meta charset="utf-8" />
        <title>@yield('title', get_setting('site_title', 'Login | Budget Management System - Admin & Dashboard Template'))</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="{{ get_setting('meta_description', 'Premium Multipurpose Admin & Dashboard Template') }}" name="description" />
        <meta content="{{ get_setting('meta_author', 'Themesbrand') }}" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

        <!-- Bootstrap Css -->
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
        <!-- App js -->
        {{-- <script src="{{ asset('assets/js/plugin.js') }}"></script> --}}
    <link rel="stylesheet" href="{{ asset('assets/fontawesome pro/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">
        @stack('head')

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* Hide numeric spin buttons globally */
            input[type="number"]::-webkit-inner-spin-button,
            input[type="number"]::-webkit-outer-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            input[type="number"] {
                -moz-appearance: textfield;
            }
        </style>

    </head>

    <body>
        @include('partials.loader')
         <main>
             @yield('content')
         </main>
        <!-- end account-pages -->

        <!-- JAVASCRIPT -->
        <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
        <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
        
        <!-- App js -->
        <script src="{{ asset('assets/js/app.js') }}"></script>

        @stack('scripts')
    </body>
</html>
