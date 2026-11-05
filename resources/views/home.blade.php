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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Calibri', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background: #F5FBFF;
            overflow-x: hidden;
            font-size: 19px;
            line-height: 1.6;
        }

        :root {
            --primary: #1976D2;
            --secondary: #42A5F5;
            --light: #E3F2FD;
            --dark: #0D47A1;
            --white: #fff;
        }

        /*================ HERO =================*/

        .hero {
            padding: 180px 0 120px;
            text-align: center;
            position: relative;
        }

        .hero h1 {
            font-size: 68px;
            font-weight: 800;
            line-height: 1.3;
            color: #1e293b;
            margin-top: 25px;
        }

        .hero h1 span {
            color: var(--primary);
        }

        .hero p {
            max-width: 760px;
            margin: 25px auto 50px;
            color: #64748B;
            font-size: 28px;
            line-height: 42px;
        }

        .hero-card {
            max-width: 950px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 15px 40px rgba(25, 118, 210, .12);
        }

        textarea {
            resize: none;
            border: 1px solid #dbeafe !important;
            border-radius: 18px !important;
            padding: 20px !important;
            font-size: 18px;
        }

        textarea:focus {
            border-color: var(--secondary) !important;
            box-shadow: 0 0 0 4px rgba(66, 165, 245, .15) !important;
        }

        .form-select {
            height: 60px;
            border-radius: 15px;
            font-size: 17px;
        }

        .form-select:focus {
            border-color: var(--secondary);
            box-shadow: none;
        }

        .btn-convert {
            height: 60px;
            border: none;
            border-radius: 15px;
            background: linear-gradient(135deg, #1976D2, #42A5F5);
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            transition: .3s;
        }

        .btn-convert:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(25, 118, 210, .25);
        }

        .hero-logo {
            width: 280px;
            position: relative;
            z-index: 2;
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-18px);
            }

            100% {
                transform: translateY(0);
            }
        }

        .circle1 {
            width: 260px;
            height: 260px;
            background: #D6EEFF;
            border-radius: 50%;
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-60%);
            opacity: .7;
            z-index: 1;
        }

        .circle2 {
            width: 170px;
            height: 170px;
            background: #B8E2FF;
            border-radius: 50%;
            position: absolute;
            bottom: 5px;
            left: 50%;
            transform: translateX(40%);
            opacity: .7;
            z-index: 1;
        }

        /*================ FEATURE =================*/

        .feature {
            padding: 110px 0;
        }

        .feature-title {
            text-align: center;
            margin-bottom: 70px;
        }

        .feature-title h2 {
            font-size: 46px;
            font-weight: 700;
            color: #1e293b;
        }

        .feature-box {
            background: white;
            padding: 45px 35px;
            border-radius: 20px;
            text-align: center;
            transition: .35s;
            box-shadow: 0 15px 35px rgba(0, 0, 0, .08);
        }

        .feature-box:hover {
            transform: translateY(-12px);
        }

        .feature-box i {
            font-size: 65px;
            color: var(--primary);
        }

        .feature-box h4 {
            margin-top: 25px;
            margin-bottom: 15px;
            font-size: 28px;
            font-weight: 700;
        }

        .feature-box p {
            font-size: 19px;
            color: #64748B;
        }

        /*================ HOW =================*/

        .how {
            padding: 110px 0;
            background: white;
        }

        .step {
            text-align: center;
        }

        .number {
            width: 75px;
            height: 75px;
            background: var(--primary);
            color: white;
            margin: auto;
            border-radius: 50%;
            line-height: 75px;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .step h4 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
        }

        .step p {
            font-size: 18px;
            color: #64748B;
        }

        @media(max-width:991px) {

            body {
                font-size: 16px;
            }

            .hero {
                padding: 140px 20px 90px;
            }

            .hero h1 {
                font-size: 46px;
            }

            .hero p {
                font-size: 22px;
                line-height: 34px;
            }

            .hero-logo {
                width: 200px;
            }

            .hero-card {
                padding: 25px;
            }

            .circle1 {
                width: 180px;
                height: 180px;
            }

            .circle2 {
                width: 110px;
                height: 110px;
            }

            .feature-title h2 {
                font-size: 36px;
            }

            .feature-box h4 {
                font-size: 24px;
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
                    <img src="{{ asset('images/logo_whalevoice_khongnen.png') }}" class="hero-logo">

                </div>

                <h1>
                    Chuyển văn bản thành
                    <span>giọng nói</span>
                </h1>

                <p class="mx-auto text-secondary" style="max-width:700px;">
                    WhaleVoice giúp chuyển đổi văn bản thành giọng nói tiếng Việt
                    nhanh chóng, tự nhiên và dễ sử dụng.
                </p>

                <div class="d-flex justify-content-center flex-wrap gap-4 mt-5">

                    <!-- Khách -->

                    <div class="card border-0 shadow-sm rounded-4 p-4" style="width:300px;">

                        <div class="text-center mb-3">
                            <i class="bi bi-person-circle fs-1 text-primary"></i>
                            <h5 class="fw-bold mt-2 mb-0">Khách</h5>
                        </div>

                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">✅ Tối đa <b>5 lượt/ngày</b></li>
                            <li class="mb-2">✅ Nghe thử âm thanh</li>
                            <li class="mb-2 text-danger">❌ Không tải file</li>
                            <li class="text-danger">❌ Không lưu lịch sử</li>
                        </ul>

                    </div>

                    <!-- Thành viên -->

                    <div class="card border-0 shadow rounded-4 p-4 position-relative"
                        style="width:320px;">

                        <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-primary px-3 py-2">
                            Miễn phí
                        </span>

                        <div class="text-center mb-3">
                            <i class="bi bi-stars fs-1 text-warning"></i>
                            <h5 class="fw-bold mt-2 mb-0">Thành viên WhaleVoice</h5>
                        </div>

                        <ul class="list-unstyled">
                            <li class="mb-2">✅ Chuyển đổi không giới hạn</li>
                            <li class="mb-2">✅ Tải file WAV</li>
                            <li class="mb-2">✅ Lưu lịch sử chuyển đổi</li>
                            <li>✅ Đồng bộ dữ liệu</li>
                        </ul>

                        @guest
                        <a href="{{ route('register') }}" class="btn btn-primary rounded-pill mt-3">
                            Đăng ký miễn phí
                        </a>
                        @endguest
                    </div>
                </div>
            </div>
            <div class="hero-card mx-auto mt-5" style="max-width:1500px;">

                @guest
                <div class="alert alert-warning rounded-4">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Bạn đang sử dụng với quyền <b>Khách</b>.
                    Mỗi ngày chỉ được chuyển đổi tối đa <b>5 lần</b>.
                    Đăng ký để sử dụng không giới hạn.
                </div>
                @endguest

                <textarea
                    id="textInput"
                    class="form-control"
                    rows="12"
                    placeholder="Hãy nhập văn bản để bắt đầu chuyển đổi thành giọng nói..."></textarea>

                <small class="text-muted mt-2 d-block">
                    <i class="bi bi-lightbulb"></i>
                    @guest
                    Khách chỉ được chuyển đổi tối đa 5 lần mỗi ngày.
                    @else
                    Bạn có thể chuyển đổi không giới hạn và tải file sau khi hoàn thành.
                    @endguest
                </small>

                <div class="row mt-4">

                    <!-- Thống kê -->
                    <div class="col-md-4">
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

                    <div class="col-md-4 d-flex align-items-end">
                        @guest
                        <button
                            id="convertBtn"
                            class="btn btn-convert w-100">
                            <i class="bi bi-volume-up-fill me-2"></i>
                            Chuyển đổi (5 lượt/ngày)
                        </button>
                        @else
                        <button
                            id="convertBtn"
                            class="btn btn-convert w-100">
                            <i class="bi bi-volume-up-fill me-2"></i>
                            Chuyển đổi
                        </button>
                        @endguest
                    </div>
                </div>
            </div>
            <div class="hero-card mx-auto mt-4 d-none" id="resultBox" style="max-width:1500px;">

                <h4 class="fw-bold mb-4">
                    <i class="bi bi-music-note-beamed me-2"></i>
                    Kết quả chuyển đổi
                </h4>

                <audio id="resultAudio" controls style="width:100%;">
                    Trình duyệt không hỗ trợ phát âm thanh.
                </audio>

                <div class="d-flex gap-3 mt-4">
                    @auth
                    <a
                        id="downloadBtn"
                        href="#"
                        download
                        class="btn btn-success">
                        <i class="bi bi-download me-2"></i>
                        Tải xuống
                    </a>
                    @else
                    <button
                        class="btn btn-success"
                        disabled>
                        <i class="bi bi-download me-2"></i>
                        Đăng nhập để tải
                    </button>
                    @endauth

                    <button
                        class="btn btn-outline-primary"
                        onclick="document.getElementById('textInput').focus()">
                        <i class="bi bi-arrow-repeat me-2"></i>
                        Chuyển đổi lại
                    </button>
                </div>
            </div>
        </div>
    </section>
    @include('layouts.footer')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const textInput = document.getElementById("textInput");
            const wordCount = document.getElementById("wordCount");
            const readTime = document.getElementById("readTime");
            const convertBtn = document.getElementById("convertBtn");
            const resultAudio = document.getElementById("resultAudio");
            const resultBox = document.getElementById("resultBox");
            const downloadBtn = document.getElementById("downloadBtn");

            let audios = [];
            let currentIndex = 0;
            let statusElement = document.createElement("p");
            let currentAudioUrl = null;

            statusElement.className = "mt-3 text-muted";
            resultAudio.insertAdjacentElement("afterend", statusElement);

            function updateStats() {
                const text = textInput.value.trim();

                if (text === "") {
                    wordCount.textContent = 0;
                    readTime.textContent = "0 giây";
                    return;
                }

                const words = text.split(/\s+/).filter(word => word.length > 0);

                wordCount.textContent = words.length;

                const seconds = Math.ceil(words.length / 150 * 60);

                if (seconds < 60) {
                    readTime.textContent = seconds + " giây";
                } else {
                    const min = Math.floor(seconds / 60);
                    const sec = seconds % 60;
                    readTime.textContent = min + " phút " + sec + " giây";
                }
            }

            textInput.addEventListener("input", updateStats);
            updateStats();

            resultAudio.addEventListener("ended", function() {
                statusElement.textContent = "Đã phát xong toàn bộ âm thanh!";
            });

            resultAudio.addEventListener("error", function() {
                statusElement.textContent = "Không thể phát file âm thanh.";
            });

            convertBtn.addEventListener("click", async function() {

                const text = textInput.value.trim();

                if (text === "") {
                    alert("Vui lòng nhập văn bản cần chuyển đổi.");
                    return;
                }

                const originalText = convertBtn.innerHTML;

                convertBtn.disabled = true;
                convertBtn.textContent = "Đang xử lý...";

                resultAudio.pause();
                resultAudio.removeAttribute("src");
                resultAudio.load();

                if (currentAudioUrl) {
                    URL.revokeObjectURL(currentAudioUrl);
                    currentAudioUrl = null;
                }
                resultBox.classList.remove("d-none");
                statusElement.textContent = "Đang tìm kiếm dữ liệu âm thanh...";

                resultBox.scrollIntoView({
                    behavior: "smooth"
                });

                try {
                    const response = await fetch("/api/tts/convert", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify({
                            text: text
                        })
                    });

                    if (!response.ok) {
                        let message = "Không thể chuyển đổi văn bản.";

                        try {
                            const errorData = await response.json();
                            message = errorData.message || message;
                        } catch (e) {
                            // Phản hồi lỗi không phải JSON
                        }

                        throw new Error(message);
                    }

                    const contentType = response.headers.get("content-type") || "";

                    if (!contentType.includes("audio/")) {
                        throw new Error("Máy chủ không trả về file âm thanh hợp lệ.");
                    }

                    const audioBlob = await response.blob();

                    if (audioBlob.size === 0) {
                        throw new Error("File âm thanh nhận được bị rỗng.");
                    }

                    currentAudioUrl = URL.createObjectURL(audioBlob);

                    resultAudio.src = currentAudioUrl;
                    resultAudio.load();

                    if (downloadBtn) {
                        downloadBtn.classList.add("d-none");
                    }

                    statusElement.textContent =
                        "Đã ghép âm thanh thành công. Đang phát toàn bộ nội dung...";

                    resultAudio.play().catch(function(error) {
                        console.error("Audio playback error:", error);

                        statusElement.textContent =
                            "Âm thanh đã sẵn sàng. Hãy nhấn nút Play để phát.";
                    });

                } catch (error) {
                    console.error("TTS Error:", error);

                    statusElement.textContent =
                        "Lỗi: " + error.message;

                } finally {
                    convertBtn.disabled = false;
                    convertBtn.innerHTML = originalText;
                }
            });
        });
    </script>
</body>

</html>