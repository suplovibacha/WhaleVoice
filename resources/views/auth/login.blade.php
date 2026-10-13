@extends('layouts.app')

@section('content')

<section class="login-page">

    <div class="container">

        <div class="row align-items-center min-vh-100">

            <!-- LEFT -->
            <div class="col-lg-6">

                <div class="login-left">

                    <div class="position-relative d-inline-block mb-4">

                        <div class="circle1"></div>
                        <div class="circle2"></div>

                        <img src="{{ asset('images/logo_whalevoice_khongnen.png') }}"
                             class="login-logo">

                    </div>

                    <h1>
                        Whale<span>Voice</span>
                    </h1>

                    <p>
                        Chuyển văn bản thành giọng nói tiếng Việt
                        nhanh chóng, tự nhiên và dễ sử dụng.
                    </p>

                </div>

            </div>

            <!-- RIGHT -->
            <div class="col-lg-6">

                <div class="login-form">

                    <h2>Đăng nhập</h2>

                    <p>
                        Chào mừng bạn quay trở lại WhaleVoice
                    </p>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                class="form-control"
                                placeholder="Nhập email">

                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                        </div>

                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Mật khẩu
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Nhập mật khẩu">

                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                        </div>

                        <div class="form-check mb-4">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="remember"
                                name="remember">

                            <label
                                class="form-check-label"
                                for="remember">

                                Ghi nhớ đăng nhập

                            </label>

                        </div>

                        <button
                            type="submit"
                            class="login-btn w-100">

                            <i class="bi bi-box-arrow-in-right me-2"></i>

                            Đăng nhập

                        </button>

                    </form>

                    @if(Route::has('register'))

                        <div class="text-center mt-4">

                            <span class="text-muted">
                                Bạn chưa có tài khoản?
                            </span>

                            <a href="{{ route('register') }}"
                               class="fw-semibold text-decoration-none text-primary">

                                Đăng ký ngay

                            </a>

                        </div>

                    @endif

                    
                </div>

            </div>

        </div>

    </div>

</section>

@endsection

<style>

.login-page{
    min-height:100vh;
    background:#F5FBFF;
    padding-top:90px;
    display:flex;
    align-items:center;
}

.login-left{
    text-align:center;
}

.login-left h1{
    font-size:58px;
    font-weight:800;
    color:#1e293b;
    margin-top:20px;
}

.login-left h1 span{
    color:#1976D2;
}

.login-left p{
    max-width:520px;
    margin:20px auto 0;
    font-size:22px;
    color:#64748B;
    line-height:36px;
}

.login-logo{
    width:240px;
    position:relative;
    z-index:2;
    animation:float 4s ease-in-out infinite;
}

.login-form{
    max-width:430px;
    width:100%;
    margin:auto;
}

.login-form h2{
    font-size:42px;
    font-weight:700;
    color:#1e293b;
    margin-bottom:10px;
}

.login-form p{
    color:#64748B;
    margin-bottom:35px;
}

.login-form label{
    margin-bottom:8px;
    color:#1e293b;
}

.login-form .form-control{
    height:58px;
    border-radius:16px;
    border:1px solid #dbeafe;
    padding:0 18px;
    font-size:16px;
}

.login-form .form-control:focus{
    border-color:#42A5F5;
    box-shadow:0 0 0 4px rgba(66,165,245,.15);
}

.login-btn{
    height:58px;
    border:none;
    border-radius:16px;
    background:linear-gradient(135deg,#1976D2,#42A5F5);
    color:#fff;
    font-weight:600;
    font-size:17px;
    transition:.3s;
}

.login-btn:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 25px rgba(25,118,210,.25);
}

.login-form a{
    text-decoration:none;
    transition:.25s;
}

.login-form a:hover{
    color:#1976D2;
}

.circle1{
    width:220px;
    height:220px;
    background:#D6EEFF;
    border-radius:50%;
    position:absolute;
    top:-20px;
    left:50%;
    transform:translateX(-60%);
    opacity:.7;
    z-index:1;
}

.circle2{
    width:140px;
    height:140px;
    background:#B8E2FF;
    border-radius:50%;
    position:absolute;
    bottom:0;
    left:50%;
    transform:translateX(40%);
    opacity:.7;
    z-index:1;
}

@keyframes float{
    0%{
        transform:translateY(0);
    }
    50%{
        transform:translateY(-18px);
    }
    100%{
        transform:translateY(0);
    }
}

@media(max-width:991px){

    .login-page{
        padding:120px 20px 60px;
    }

    .login-left{
        margin-bottom:60px;
    }

    .login-left h1{
        font-size:42px;
    }

    .login-left p{
        font-size:18px;
        line-height:30px;
    }

    .login-logo{
        width:180px;
    }

    .login-form{
        max-width:100%;
    }

}

</style>