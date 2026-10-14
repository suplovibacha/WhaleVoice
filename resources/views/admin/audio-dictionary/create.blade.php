@extends('layouts.admin')

@section('title', 'Thêm từ điển âm thanh')

@section('content')

<div class="card-custom">

    <h4 class="fw-bold mb-4">
        Thêm từ mới
    </h4>

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
                <small class="text-danger">{{ $message }}</small>
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
                <small class="text-danger d-block">{{ $message }}</small>
            @enderror

        </div>

        <!-- Nghe thử -->

        <div class="mb-4 d-none" id="previewBox">

            <label class="form-label fw-semibold">
                Nghe thử
            </label>

            <audio
                id="audioPreview"
                controls
                style="width:100%;">

                Trình duyệt không hỗ trợ phát âm thanh.

            </audio>

        </div>

        <div class="d-flex gap-2">

            <button
                type="submit"
                class="btn btn-success">

                <i class="bi bi-floppy-fill me-1"></i>

                Lưu

            </button>

            <a
                href="{{ route('admin.audio-dictionary.index') }}"
                class="btn btn-secondary">

                <i class="bi bi-arrow-left me-1"></i>

                Quay lại

            </a>

        </div>

    </form>

</div>

<script>

document.getElementById('audioFile').addEventListener('change', function () {

    const file = this.files[0];

    if (!file) return;

    const url = URL.createObjectURL(file);

    document.getElementById('audioPreview').src = url;

    document.getElementById('previewBox').classList.remove('d-none');

});

</script>

@endsection