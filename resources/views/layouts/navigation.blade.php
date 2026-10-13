<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
            <img src="{{ asset('images/logo_whalevoice_khongnen_khongchunho.png') }}" height="55" width="75" class="me-2">
            <span class="fw-bold text-primary">WhaleVoice</span>
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav ms-auto align-items-center">

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}">
                        Trang chủ
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#">
                        Giới thiệu
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('contact') }}">
                        Liên hệ
                    </a>
                </li>

                @auth

                    <li class="nav-item dropdown ms-3">

                        <a class="nav-link dropdown-toggle"
                           href="#"
                           data-bs-toggle="dropdown">

                            <i class="bi bi-person-circle me-1"></i>
                            {{ auth()->user()->name }}

                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">

                            <li>
                                <a class="dropdown-item" href="{{ route('dashboard') }}">
                                    Trang cá nhân
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        Đăng xuất
                                    </button>
                                </form>
                            </li>

                        </ul>

                    </li>

                @else

                    <li class="nav-item ms-3">
                        <a href="{{ route('login') }}" class="btn btn-primary rounded-pill px-4">
                            Đăng nhập
                        </a>
                    </li>

                @endauth

            </ul>

        </div>

    </div>
</nav>