<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>WhaleVoice - Text To Speech</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icon -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
        font-family:'Calibri',sans-serif;
    }

    html{
        scroll-behavior:smooth;
    }

    body{
        background:#F5FBFF;
        overflow-x:hidden;
        font-size:19px;
        line-height:1.6;
    }

    :root{
        --primary:#1976D2;
        --secondary:#42A5F5;
        --light:#E3F2FD;
        --dark:#0D47A1;
        --white:#fff;
    }

    /*================ HERO =================*/

    .hero{
        padding:180px 0 120px;
        text-align:center;
        position:relative;
    }

    .hero h1{
        font-size:68px;
        font-weight:800;
        line-height:1.3;
        color:#1e293b;
        margin-top:25px;
    }

    .hero h1 span{
        color:var(--primary);
    }

    .hero p{
        max-width:760px;
        margin:25px auto 50px;
        color:#64748B;
        font-size:28px;
        line-height:42px;
    }

    .hero-card{
        max-width:950px;
        margin:0 auto;
        background:#fff;
        padding:40px;
        border-radius:25px;
        box-shadow:0 15px 40px rgba(25,118,210,.12);
    }

    textarea{
        resize:none;
        border:1px solid #dbeafe !important;
        border-radius:18px !important;
        padding:20px !important;
        font-size:18px;
    }

    textarea:focus{
        border-color:var(--secondary) !important;
        box-shadow:0 0 0 4px rgba(66,165,245,.15) !important;
    }

    .form-select{
        height:60px;
        border-radius:15px;
        font-size:17px;
    }

    .form-select:focus{
        border-color:var(--secondary);
        box-shadow:none;
    }

    .btn-convert{
        height:60px;
        border:none;
        border-radius:15px;
        background:linear-gradient(135deg,#1976D2,#42A5F5);
        color:#fff;
        font-size:18px;
        font-weight:600;
        transition:.3s;
    }

    .btn-convert:hover{
        transform:translateY(-2px);
        box-shadow:0 10px 25px rgba(25,118,210,.25);
    }

    .hero-logo{
        width:280px;
        position:relative;
        z-index:2;
        animation:float 4s ease-in-out infinite;
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

    .circle1{
        width:260px;
        height:260px;
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
        width:170px;
        height:170px;
        background:#B8E2FF;
        border-radius:50%;
        position:absolute;
        bottom:5px;
        left:50%;
        transform:translateX(40%);
        opacity:.7;
        z-index:1;
    }

    /*================ FEATURE =================*/

    .feature{
        padding:110px 0;
    }

    .feature-title{
        text-align:center;
        margin-bottom:70px;
    }

    .feature-title h2{
        font-size:46px;
        font-weight:700;
        color:#1e293b;
    }

    .feature-box{
        background:white;
        padding:45px 35px;
        border-radius:20px;
        text-align:center;
        transition:.35s;
        box-shadow:0 15px 35px rgba(0,0,0,.08);
    }

    .feature-box:hover{
        transform:translateY(-12px);
    }

    .feature-box i{
        font-size:65px;
        color:var(--primary);
    }

    .feature-box h4{
        margin-top:25px;
        margin-bottom:15px;
        font-size:28px;
        font-weight:700;
    }

    .feature-box p{
        font-size:19px;
        color:#64748B;
    }

    /*================ HOW =================*/

    .how{
        padding:110px 0;
        background:white;
    }

    .step{
        text-align:center;
    }

    .number{
        width:75px;
        height:75px;
        background:var(--primary);
        color:white;
        margin:auto;
        border-radius:50%;
        line-height:75px;
        font-size:28px;
        font-weight:bold;
        margin-bottom:20px;
    }

    .step h4{
        font-size:28px;
        font-weight:700;
        margin-bottom:15px;
    }

    .step p{
        font-size:18px;
        color:#64748B;
    }

    @media(max-width:991px){

        body{
            font-size:16px;
        }

        .hero{
            padding:140px 20px 90px;
        }

        .hero h1{
            font-size:46px;
        }

        .hero p{
            font-size:22px;
            line-height:34px;
        }

        .hero-logo{
            width:200px;
        }

        .hero-card{
            padding:25px;
        }

        .circle1{
            width:180px;
            height:180px;
        }

        .circle2{
            width:110px;
            height:110px;
        }

        .feature-title h2{
            font-size:36px;
        }

        .feature-box h4{
            font-size:24px;
        }
    }

</style>
</head>

<body>
@include('layouts.navigation')

<section class="hero">

    <div class="container">

        <div class="text-center">

            <div class="position-relative d-inline-block mb-4">

                <div class="circle1"></div>

                <div class="circle2"></div>

                <img src="{{ asset('images/logo_whalevoice_khongnen.png') }}"
                     class="hero-logo">

            </div>

            <h1>
                Chuyển văn bản thành
                <span>giọng nói</span>
            </h1>

            <p class="mx-auto" style="max-width:700px;">
                WhaleVoice giúp chuyển đổi văn bản thành giọng nói tiếng Việt
                nhanh chóng, tự nhiên và dễ sử dụng.
            </p>

        </div>

        <div class="hero-card mx-auto mt-5" style="max-width:1500px;">

            <textarea
                id="textInput"
                class="form-control"
                rows="12"
                placeholder="Nhập văn bản..."></textarea>

            <div class="row mt-4">

                <!-- Thống kê -->
                <div class="col-md-4">

                    <!-- <label class="fw-semibold mb-2">
                        Thống kê văn bản
                    </label> -->

                    <div class="border rounded-4 p-3 bg-light h-100">

                        <div class="d-flex justify-content-between mb-2">

                            <span>Số từ</span>

                            <strong id="wordCount">0</strong>

                        </div>

                        <div class="d-flex justify-content-between">

                            <span>Thời gian đọc</span>

                            <strong id="readTime">0 giây</strong>

                        </div>

                    </div>

                </div>

                <!-- Định dạng -->
                <div class="col-md-4">

                    <label class="fw-semibold mb-2">
                        Định dạng âm thanh
                    </label>

                    <select class="form-select">
                        <option>WAV</option>
                    </select>

                </div>

                <!-- Nút -->
                <div class="col-md-4 d-flex align-items-end">

                    <button class="btn btn-convert w-100">
                        <i class="bi bi-volume-up-fill me-2"></i>
                        Chuyển đổi
                    </button>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- <section class="container my-5">
    <div class="hero-card">
        <h3 class="mb-4">
            Kết quả chuyển đổi
        </h3>
        <audio controls class="w-100">
            <source src="" type="audio/wav">
        </audio>
        <div class="row mt-4">
            <div class="col-md-6 d-grid">
                <button class="btn btn-success">
                    <i class="bi bi-download me-2"></i>
                    Tải xuống
                </button>
            </div>
            <div class="col-md-6 d-grid">
                <button class="btn btn-outline-primary">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Chuyển đổi lại
                </button>
            </div>
        </div>
    </div>
</section> -->
@include('layouts.footer')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const textInput = document.getElementById("textInput");
    const wordCount = document.getElementById("wordCount");
    const readTime = document.getElementById("readTime");

    function updateStats(){

        const text = textInput.value.trim();

        if(text === ""){
            wordCount.innerHTML = 0;
            readTime.innerHTML = "0 giây";
            return;
        }

        // Đếm số từ
        const words = text.split(/\s+/).filter(word => word.length > 0);

        wordCount.innerHTML = words.length;

        // Ước tính đọc 150 từ/phút
        const minutes = words.length / 150;

        if(minutes < 1){

            readTime.innerHTML = Math.ceil(minutes * 60) + " giây";

        }else{

            const min = Math.floor(minutes);
            const sec = Math.round((minutes - min) * 60);

            readTime.innerHTML = min + " phút " + sec + " giây";

        }

    }

    textInput.addEventListener("input", updateStats);

    updateStats();

});
</script>
</body>

</html>