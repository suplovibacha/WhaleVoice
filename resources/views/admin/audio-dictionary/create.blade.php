@extends('layouts.admin')

@section('title', 'Thêm từ điển âm thanh')

@section('content')

<div class="card-custom">

    <h4 class="fw-bold mb-4">
        Thêm từ điển âm thanh
    </h4>

    <ul class="nav nav-tabs mb-4" id="audioTab" role="tablist">

        <li class="nav-item">

            <button class="nav-link active"
                    data-bs-toggle="tab"
                    data-bs-target="#manual">

                <i class="bi bi-plus-circle me-1"></i>

                Thêm thủ công

            </button>

        </li>

        <li class="nav-item">

            <button class="nav-link"
                    data-bs-toggle="tab"
                    data-bs-target="#excel">

                <i class="bi bi-file-earmark-excel me-1"></i>

                Import Excel

            </button>

        </li>

    </ul>

    <div class="tab-content">

        <!-- THÊM THỦ CÔNG -->

        <div class="tab-pane fade show active" id="manual">

            <form action="{{ route('admin.audio-dictionary.store') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="mb-4">

                    <label class="form-label fw-semibold">
                        Từ
                    </label>

                    <input
                        type="text"
                        name="word"
                        value="{{ old('word') }}"
                        class="form-control"
                        placeholder="Ví dụ: anh"
                        required>

                    @error('word')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

                <div class="mb-4">

                    <label class="form-label fw-semibold">
                        File âm thanh
                    </label>

                    <input
                        type="file"
                        id="audioFile"
                        name="audio"
                        class="form-control"
                        accept=".wav,.mp3"
                        required>

                    <small class="text-muted">
                        Chỉ chấp nhận file WAV hoặc MP3.
                    </small>

                    @error('audio')
                        <small class="text-danger d-block">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

                <div class="mb-4 d-none" id="previewBox">

                    <label class="form-label fw-semibold">
                        Nghe thử
                    </label>

                    <audio
                        id="audioPreview"
                        controls
                        style="width:100%">

                    </audio>

                </div>

                <button
                    type="submit"
                    class="btn btn-success">

                    <i class="bi bi-floppy-fill me-1"></i>

                    Lưu

                </button>

            </form>

        </div>

        <!-- IMPORT EXCEL -->

        <div class="tab-pane fade" id="excel">

            <form action="{{ route('admin.audio-dictionary.import') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="alert alert-info">

                    <strong>Định dạng Excel:</strong>

                    <br>

                    <table class="table table-bordered mt-2 mb-0">

                        <thead>

                        <tr>

                            <th>Từ</th>

                            <th>Tên file âm thanh</th>

                        </tr>

                        </thead>

                        <tbody>

                        <tr>

                            <td>anh</td>

                            <td>anh.wav</td>

                        </tr>

                        <tr>

                            <td>ba</td>

                            <td>ba.mp3</td>

                        </tr>

                        </tbody>

                    </table>

                </div>

                <div class="mb-3">

                    <label class="form-label fw-semibold">

                        File Excel

                    </label>

                    <input
                        type="file"
                        name="excel"
                        class="form-control"
                        accept=".xlsx,.xls,.csv"
                        required>

                </div>

                <button
                    class="btn btn-success">

                    <i class="bi bi-upload me-1"></i>

                    Import Excel

                </button>

            </form>

        </div>

    </div>

    <hr class="my-4">

    <a href="{{ route('admin.audio-dictionary.index') }}"
       class="btn btn-secondary">

        <i class="bi bi-arrow-left me-1"></i>

        Quay lại

    </a>

</div>

<script>

document.getElementById('audioFile').addEventListener('change', function () {

    const file = this.files[0];

    if (!file) return;

    document.getElementById('audioPreview').src = URL.createObjectURL(file);

    document.getElementById('previewBox').classList.remove('d-none');

});

</script>

@endsection