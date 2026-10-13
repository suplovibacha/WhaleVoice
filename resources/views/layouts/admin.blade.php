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

        body{
            background:#F5FBFF;
            overflow-x:hidden;
        }

        :root{
            --primary:#1976D2;
            --secondary:#42A5F5;
            --sidebar:#1565C0;
            --white:#fff;
            --text:#1e293b;
        }

        /* Sidebar */

        .sidebar{

            position:fixed;
            left:0;
            top:0;

            width:260px;
            height:100vh;

            background:linear-gradient(180deg,#1976D2,#1565C0);

            color:#fff;

            z-index:999;

        }

        .logo{

            height:80px;

            display:flex;
            align-items:center;
            justify-content:center;

            gap:12px;

            border-bottom:1px solid rgba(255,255,255,.12);

        }

        .logo img{

            width:48px;

        }

        .logo h4{

            margin:0;
            font-weight:700;

        }

        .menu{

            padding:25px 15px;

        }

        .menu a{

            display:flex;

            align-items:center;

            gap:15px;

            color:white;

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

        /* Navbar */

        .topbar{

            height:80px;

            background:white;

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

        .content{

            padding:35px;

        }

        .card-custom{

            background:white;

            border:none;

            border-radius:20px;

            box-shadow:0 10px 25px rgba(0,0,0,.05);

            padding:25px;

        }

    </style>

</head>

<body>

    <!-- Sidebar -->

    <div class="sidebar">

        <div class="logo">

            <img src="{{ asset('images/logo_whalevoice_khongnen.png') }}">

            <h4>WhaleVoice</h4>

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

            <!-- <a href="#">
                <i class="bi bi-file-earmark-text-fill"></i>
                Lịch sử
            </a> -->

            <a href="#">
                <i class="bi bi-mic-fill"></i>
                Giọng đọc
            </a>

            <!-- <a href="#">
                <i class="bi bi-bar-chart-fill"></i>
                Thống kê
            </a> -->

            <!-- <a href="#">
                <i class="bi bi-gear-fill"></i>
                Cài đặt
            </a> -->

            <hr class="text-white">

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button class="btn text-white w-100 text-start">

                    <i class="bi bi-box-arrow-right me-2"></i>

                    Đăng xuất

                </button>

            </form>

        </div>

    </div>

    <!-- Main -->

    <div class="main">

        <!-- Navbar -->

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

        <!-- Content -->

        <div class="content">

            @yield('content')

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>