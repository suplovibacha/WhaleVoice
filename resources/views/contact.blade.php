<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Liên hệ - WhaleVoice</title>

    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    background:#F5FBFF;
}

.contact-page{
    display:flex;
    min-height:calc(100vh - 95px);
    margin-top:95px;
}

/* ================= LEFT ================= */

.left-box{
    width:45%;
    /* background:#ffffff; */
    display:flex;
    justify-content:center;
    align-items:center;
    padding:80px;
}

.form-area{
    width:100%;
    max-width:520px;
}

h3{
    color:#1976D2;
    font-size:42px;
    font-weight:700;
}

.subtitle{
    color:#6B7280;
    font-size:17px;
    line-height:30px;
    margin:18px 0 45px;
}

/* ================= CONTACT INFO ================= */

.info{
    display:flex;
    align-items:center;
    gap:18px;
    margin-bottom:28px;
}

.info i{
    width:60px;
    height:60px;
    border-radius:18px;
    background:#EAF6FF;
    color:#1976D2;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:24px;
    flex-shrink:0;
    transition:.3s;
}

.info:hover i{
    background:#1976D2;
    color:#fff;
}

.info h6{
    margin-bottom:6px;
    font-size:18px;
    font-weight:700;
    color:#1F2937;
}

.info p{
    margin:0;
    color:#6B7280;
    font-size:16px;
}

/* ================= RIGHT ================= */

.right-box{
    width:55%;
    /* background:#EAF7FF; */
    display:flex;
    justify-content:center;
    align-items:center;
    padding:45px;
}

.map-box{
    width:100%;
    height:650px;
    border-radius:25px;
    overflow:hidden;
    background:#fff;
    box-shadow:0 18px 40px rgba(0,0,0,.08);
}

.map-box iframe{
    width:100%;
    height:100%;
    border:0;
}

/* ================= RESPONSIVE ================= */

@media(max-width:992px){

    .contact-page{
        flex-direction:column;
    }

    .left-box,
    .right-box{
        width:100%;
        padding:50px 30px;
    }

    .map-box{
        height:420px;
    }

    h3{
        font-size:34px;
    }

}

</style>

</head>

<body>

@include('layouts.navigation')

<div class="contact-page">

    <!-- Cột trái -->
    <div class="left-box">

        <div class="form-area">

            <h3>Liên hệ với WhaleVoice</h3>

            <p class="subtitle">
                Chúng tôi luôn sẵn sàng hỗ trợ và lắng nghe mọi góp ý từ bạn.
            </p>

            <div class="info">
                <i class="bi bi-envelope-fill"></i>
                <div>
                    <h6>Email</h6>
                    <p>whalevoice@gmail.com</p>
                </div>
            </div>

            <div class="info">
                <i class="bi bi-telephone-fill"></i>
                <div>
                    <h6>Hotline</h6>
                    <p>0123 456 789</p>
                </div>
            </div>

            <div class="info">
                <i class="bi bi-geo-alt-fill"></i>
                <div>
                    <h6>Địa chỉ</h6>
                    <p>Cần Thơ, Việt Nam</p>
                </div>
            </div>

            <div class="info">
                <i class="bi bi-clock-fill"></i>
                <div>
                    <h6>Giờ làm việc</h6>
                    <p>08:00 - 17:00 (Thứ 2 - Thứ 7)</p>
                </div>
            </div>

        </div>

    </div>

    <!-- Cột phải -->
    <div class="right-box">

        <div class="map-box">

            <iframe
                src="https://www.google.com/maps?q=Can%20Tho%20Vietnam&output=embed"
                allowfullscreen=""
                loading="lazy">
            </iframe>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

@include('layouts.footer')
</body>

</html>
