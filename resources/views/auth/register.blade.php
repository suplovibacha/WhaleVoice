@extends('layouts.app')

@section('content')

<section class="register-page">

    <div class="container">

        <div class="row align-items-center min-vh-100">

            <!-- LEFT -->
            <div class="col-lg-6">

                <div class="register-left">

                    <div class="position-relative d-inline-block mb-4">

                        <div class="circle1"></div>
                        <div class="circle2"></div>

                        <img src="{{ asset('images/logo_whalevoice_khongnen.png') }}"
                             class="register-logo">

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

                <div class="register-form">

                    <h2>Đăng ký</h2>

                    <p>
                        Tạo tài khoản để bắt đầu sử dụng WhaleVoice
                    </p>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Họ và tên
                            </label>

                            <input
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                class="form-control"
                                placeholder="Nhập họ và tên"
                                required
                                autofocus
                                autocomplete="name">

                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Loại tài khoản
                            </label>

                            <select
                                name="role"
                                id="role"
                                class="form-control">

                                <option value="Người dùng">Người dùng</option>
                                <option value="Nhân viên">Nhân viên</option>

                            </select>
                        </div>

                        <div class="mb-3 d-none" id="employeeBox">

                            <label class="form-label fw-semibold">
                                Mã nhân viên
                            </label>

                            <input
                                type="text"
                                name="employee_code"
                                class="form-control"
                                placeholder="Nhập mã nhân viên">

                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                class="form-control"
                                placeholder="Nhập email"
                                required
                                autocomplete="username">

                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Mật khẩu
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Nhập mật khẩu"
                                required
                                autocomplete="new-password">

                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                        </div>

                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Xác nhận mật khẩu
                            </label>

                            <input
                                type="password"
                                name="password_confirmation"
                                class="form-control"
                                placeholder="Nhập lại mật khẩu"
                                required
                                autocomplete="new-password">

                            @error('password_confirmation')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                        </div>

                        <button
                            type="submit"
                            class="register-btn w-100">

                            <i class="bi bi-person-plus-fill me-2"></i>

                            Đăng ký

                        </button>

                    </form>

                    <div class="text-center mt-4">

                        <span class="text-muted">
                            Đã có tài khoản?
                        </span>

                        <a href="{{ route('login') }}"
                           class="fw-semibold text-decoration-none text-primary">

                            Đăng nhập ngay

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection

<script>
document.addEventListener("DOMContentLoaded", function () {

    const role = document.getElementById("role");
    const employeeBox = document.getElementById("employeeBox");

    function toggleEmployeeBox() {

        if (role.value === "Nhân viên") {
            employeeBox.classList.remove("d-none");
        } else {
            employeeBox.classList.add("d-none");
        }

    }

    toggleEmployeeBox();

    role.addEventListener("change", toggleEmployeeBox);

});
</script>

<style>

.register-page{
    min-height:100vh;
    background:#F5FBFF;
    display:flex;
    align-items:center;
    padding:100px 0 40px;
}

.register-left{
    text-align:center;
}

.register-left h1{
    font-size:58px;
    font-weight:800;
    color:#1e293b;
    margin-top:20px;
}

.register-left h1 span{
    color:#1976D2;
}

.register-left p{
    max-width:520px;
    margin:20px auto 0;
    font-size:22px;
    color:#64748B;
    line-height:36px;
}

.register-logo{
    width:240px;
    position:relative;
    z-index:2;
    animation:float 4s ease-in-out infinite;
}

.register-form{
    max-width:460px;
    margin:auto;
}

.register-form h2{
    font-size:42px;
    font-weight:700;
    color:#1e293b;
    margin-bottom:10px;
}

.register-form p{
    color:#64748B;
    margin-bottom:35px;
}

.register-form .form-label{
    font-weight:600;
    color:#1e293b;
    margin-bottom:8px;
}

.register-form .form-control{
    height:58px;
    border:1px solid #dbeafe;
    border-radius:15px;
    padding:0 18px;
    font-size:16px;
    transition:.3s;
}

.register-form .form-control:focus{
    border-color:#42A5F5;
    box-shadow:0 0 0 4px rgba(66,165,245,.15);
}

.register-btn{
    width:100%;
    height:58px;
    border:none;
    border-radius:15px;
    background:linear-gradient(135deg,#1976D2,#42A5F5);
    color:#fff;
    font-size:17px;
    font-weight:600;
    transition:.3s;
}

.register-btn:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 25px rgba(25,118,210,.25);
}

.register-form a{
    text-decoration:none;
}

.register-form a:hover{
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

    .register-page{
        padding:120px 20px 60px;
    }

    .register-left{
        margin-bottom:60px;
    }

    .register-left h1{
        font-size:42px;
    }

    .register-left p{
        font-size:18px;
        line-height:30px;
    }

    .register-logo{
        width:180px;
    }

    .register-form{
        max-width:100%;
    }

}

</style>