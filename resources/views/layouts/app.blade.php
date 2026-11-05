<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>WhaleVoice</title>
    <!-- Logo trên tab trình duyệt -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo_whalevoice_khongnen_khongchunho.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_whalevoice_khongnen_khongchunho.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>

        body{
            font-family:'Manrope',sans-serif;
            background:#f8fbff;
        }

    </style>

</head>

<body>

    @include('layouts.navigation')

    <main>

        @yield('content')

    </main>

    @include('layouts.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>