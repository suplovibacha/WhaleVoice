<footer class="footer">

    <div class="container">

        <div class="row gy-5">

            <!-- Logo -->
            <div class="col-lg-4">

                <img src="{{ asset('images/logo_whalevoice_khongnen.png') }}"
                     alt="WhaleVoice"
                     width="180">

                <p class="mt-4">
                    WhaleVoice là nền tảng chuyển đổi văn bản thành giọng nói bằng
                    trí tuệ nhân tạo (AI), mang đến giọng đọc tự nhiên, nhanh chóng
                    và dễ sử dụng cho mọi người.
                </p>

                <div class="social">

                    <a href="#">
                        <i class="bi bi-facebook"></i>
                    </a>

                    <a href="#">
                        <i class="bi bi-github"></i>
                    </a>

                    <a href="#">
                        <i class="bi bi-youtube"></i>
                    </a>

                    <a href="#">
                        <i class="bi bi-envelope-fill"></i>
                    </a>

                </div>

            </div>

            <!-- Liên kết -->
            <div class="col-lg-2">

                <h5>Liên kết</h5>

                <ul class="footer-link">

                    <li>
                        <a href="{{ url('/') }}">
                            <i class="bi bi-house-door-fill me-2"></i>
                            Trang chủ
                        </a>
                    </li>

                    <li>
                        <a href="#gioithieu">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Giới thiệu
                        </a>
                    </li>

                    <li>
                        <a href="#huongdan">
                            <i class="bi bi-book-fill me-2"></i>
                            Hướng dẫn
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('contact') }}">
                            <i class="bi bi-chat-dots-fill me-2"></i>
                            Liên hệ
                        </a>
                    </li>

                </ul>

            </div>

            <!-- Dịch vụ -->
            <div class="col-lg-3">

                <h5>Dịch vụ</h5>

                <ul class="footer-link">

                    <li>
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Chuyển văn bản thành giọng nói
                    </li>

                    <li>
                        <i class="bi bi-check-circle-fill me-2"></i>
                        AI Voice Tiếng Việt
                    </li>

                    <li>
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Tải xuống MP3
                    </li>

                    <li>
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Nhiều giọng đọc
                    </li>

                </ul>

            </div>

            <!-- Liên hệ -->
            <div class="col-lg-3">

                <h5>Thông tin liên hệ</h5>

                <p>
                    <i class="bi bi-envelope-fill me-2"></i>
                    whalevoice@gmail.com
                </p>

                <p>
                    <i class="bi bi-telephone-fill me-2"></i>
                    0123 456 789
                </p>

                <p>
                    <i class="bi bi-geo-alt-fill me-2"></i>
                    Cần Thơ, Việt Nam
                </p>

                <p>
                    <i class="bi bi-clock-fill me-2"></i>
                    Thứ 2 - Chủ nhật
                </p>

            </div>

        </div>

        <hr>

        <div class="row align-items-center">

            <div class="col-md-6 text-center text-md-start">

                © {{ date('Y') }}
                <strong>WhaleVoice</strong>.
                All Rights Reserved.

            </div>

            <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">

                Made with
                <i class="bi bi-heart-fill text-danger"></i>
                by WhaleVoice Team

            </div>

        </div>

    </div>

</footer>

<style>

.footer{
    background:linear-gradient(135deg,#EAF8FF,#D5F0FF);
    color:#374151;
    padding:70px 0 25px;
    margin-top:80px;
    border-top:1px solid #C8E9FF;
}

.footer img{
    margin-bottom:15px;
}

.footer h5{
    color:#1976D2;
    font-size:20px;
    font-weight:700;
    margin-bottom:22px;
    position:relative;
}

.footer h5::after{
    content:"";
    width:50px;
    height:3px;
    background:#42A5F5;
    border-radius:20px;
    display:block;
    margin-top:8px;
}

.footer p{
    color:#5B6472;
    line-height:30px;
    font-size:15px;
    margin-bottom:14px;
}

.footer-link{
    list-style:none;
    padding:0;
    margin:0;
}

.footer-link li{
    margin-bottom:15px;
}

.footer-link a{
    color:#5B6472;
    text-decoration:none;
    transition:.3s;
    display:inline-flex;
    align-items:center;
}

.footer-link a:hover{
    color:#1976D2;
    transform:translateX(5px);
}

.footer i{
    color:#42A5F5;
}

.footer hr{
    border-color:#CFEAFF;
    opacity:1;
    margin:45px 0 20px;
}

.footer .social{
    display:flex;
    gap:12px;
    margin-top:28px;
}

.footer .social a{
    width:46px;
    height:46px;
    border-radius:50%;
    background:#fff;
    color:#1976D2;
    display:flex;
    justify-content:center;
    align-items:center;
    text-decoration:none;
    font-size:18px;
    box-shadow:0 8px 20px rgba(25,118,210,.12);
    transition:.3s;
}

.footer .social a:hover{
    background:#1976D2;
    color:#fff;
    transform:translateY(-5px);
}

.footer .text-center{
    color:#6B7280;
    font-size:15px;
}

.footer strong{
    color:#1976D2;
}

@media(max-width:991px){

    .footer{
        text-align:center;
    }

    .footer h5::after{
        margin:10px auto 0;
    }

    .footer .social{
        justify-content:center;
    }

}

</style>