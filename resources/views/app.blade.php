<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ $meta['title'] ?? 'Thiếu Nhi Thánh Thể Giáo Xứ Trù Mật' }}">
        <meta property="og:description" content="{{ $meta['description'] ?? 'Trang thông tin, hoạt động và học tập của Xứ Đoàn Thiếu Nhi Thánh Thể Giáo xứ Trù Mật.' }}">
        <meta property="og:image" content="{{ $meta['image'] ?? asset('apple-touch-icon.png') }}">
        <meta property="og:url" content="{{ $meta['url'] ?? request()->url() }}">
        <meta property="og:site_name" content="Thiếu Nhi Thánh Thể Giáo xứ Trù Mật">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $meta['title'] ?? 'Thiếu Nhi Thánh Thể Giáo Xứ Trù Mật' }}">
        <meta name="twitter:description" content="{{ $meta['description'] ?? 'Trang thông tin, hoạt động và học tập của Xứ Đoàn Thiếu Nhi Thánh Thể Giáo xứ Trù Mật.' }}">
        <meta name="twitter:image" content="{{ $meta['image'] ?? asset('apple-touch-icon.png') }}">
        <script>
            (function () {
                try {
                    var k = 'tntt-color-scheme';
                    var s = localStorage.getItem(k);
                    var root = document.documentElement;
                    if (s === 'dark') {
                        root.classList.add('dark');
                    } else if (s === 'light') {
                        root.classList.remove('dark');
                    } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        root.classList.add('dark');
                    } else {
                        root.classList.remove('dark');
                    }
                } catch (e) {}
            })();
        </script>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
