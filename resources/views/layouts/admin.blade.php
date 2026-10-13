<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>WhaleVoice Admin</title>

    <link rel="icon" href="{{ asset('images/logo_whalevoice_khongnen.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Manrope',sans-serif;
        }

        :root{
            --primary:#1976D2;
            --secondary:#42A5F5;
            --sidebar:#1565C0;
            --white:#fff;
            --text:#1e293b;
        }

        body{
            background:#F5FBFF;
            overflow-x:hidden;
        }

        /* Sidebar */

        .sidebar{
            position:fixed;
            top:0;
            left:0;
            width:260px;
            height:100vh;
            background:linear-gradient(180deg,#9ddcff99,#4aa2f9);
            color:#fff;
            z-index:999;
        }

        .logo{
            display:flex;
            flex-direction:column;
            justify-content:center;
            align-items:center;
            padding:5px 20px 30px;
            border-bottom:2px solid rgba(255,255,255,.15);
        }

        .logo img{
            width:200px;
            margin-bottom:2px;
        }

        /* .logo h4{
            margin:0;
            font-size:24px;
            font-weight:700;
            color:#fff;
            letter-spacing:.5px;
        } */

        .menu{
            padding:25px 15px;
        }

        .menu a{
            display:flex;
            align-items:center;
            gap:15px;
            color:#fff;
            text-decoration:none;
            padding:14px 18px;
            margin-bottom:10px;
            border-radius:14px;
            transition:.3s;
            font-size:16px;
        }

        .menu a:hover{
            background:rgba(255,255,255,.18);
        }

        .menu i{
            font-size:20px;
        }

        /* Main */

        .main{
            margin-left:260px;
            min-height:100vh;
        }

        /* Topbar */

        .topbar{
            height:80px;
            background:#fff;
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:0 35px;
            box-shadow:0 5px 20px rgba(0,0,0,.05);
        }

        .topbar h3{
            margin:0;
            font-weight:700;
            color:#1e293b;
        }

        .admin-info{
            display:flex;
            align-items:center;
            gap:15px;
        }

        .admin-info img{
            width:42px;
            height:42px;
            border-radius:50%;
        }

        /* Content */

        .content{
            padding:35px;
        }

        .card-custom{
            background:#fff;
            border:none;
            border-radius:20px;
            box-shadow:0 10px 25px rgba(0,0,0,.05);
            padding:25px;
        }
    </style>

</head>

<body>

<div class="sidebar">

    <div class="logo">
        <img src="{{ asset('images/logo_whalevoice_khongnen.png') }}" alt="Logo">
    </div>

    <div class="menu">

        <a href="{{ route('admin.dashboard') }}">
            <i class="bi bi-grid-fill"></i>
            Dashboard
        </a>

        <a href="{{ route('admin.accounts.index') }}">
            <i class="bi bi-people-fill"></i>
            Quản lý tài khoản
        </a>

        <a href="#">
            <i class="bi bi-mic-fill"></i>
            Quản lý giọng đọc
        </a>

        <hr class="text-white">

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="btn text-white w-100 text-start">
                <i class="bi bi-box-arrow-right me-2"></i>
                Đăng xuất
            </button>

        </form>

    </div>

</div>

<div class="main">

    <div class="topbar">

        <h3>@yield('title')</h3>

        <div class="admin-info">

            <i class="bi bi-bell fs-5"></i>

            <span>
                Xin chào,
                <strong>{{ auth()->user()->name }}</strong>
            </span>

            <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=1976D2&color=fff">

        </div>

    </div>

    <div class="content">
        @yield('content')
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>