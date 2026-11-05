@extends('layouts.app')

@section('title','Từ điển âm thanh')

@section('content')

<style>
    .dictionary-page {
        margin-top: 90px;
    }

    .alphabet {
        position: sticky;
        top: 95px;
    }

    .alphabet-title {
        font-weight: 700;
        color: #3b82f6;
        margin-bottom: 15px;
        font-size: 18px;
    }

    .alphabet a {
        display: block;
        text-decoration: none;
        color: #64748b;
        padding: 8px 12px;
        border-radius: 10px;
        margin-bottom: 5px;
        transition: .25s;
        font-weight: 600;
    }

    .alphabet a:hover,
    .alphabet a.active {
        background: #e0f2fe;
        color: #1976D2;
    }

    .dictionary-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .dictionary-header h2 {
        font-weight: 700;
        color: #1976D2;
        margin: 0;
    }

    .search-box {
        width: 330px;
    }

    .word-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 0;
        border-bottom: 1px solid #edf2f7;
    }

    .word-item:last-child {
        border-bottom: none;
    }

    .word-title {
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
    }

    .word-number {
        width: 40px;
        color: #94a3b8;
        font-weight: 600;
    }

    audio {
        width: 240px;
        height: 36px;
    }
</style>

<div class="container dictionary-page">

    <div class="row">

        <!-- Sidebar -->

        <div class="col-lg-2">

            <div class="alphabet">

                <div class="alphabet-title">
                    Bảng chữ cái
                </div>

                <a href="{{ route('dictionary.index') }}"
                    class="{{ request('letter')=='' ? 'active' : '' }}">
                    Tất cả
                </a>

                @php
                $letters = [
                'A', 'Ă', 'Â', 'B', 'C', 'D', 'Đ',
                'E', 'Ê', 'G', 'H', 'I', 'K', 'L',
                'M', 'N', 'O', 'Ô', 'Ơ', 'P', 'Q',
                'R', 'S', 'T', 'U', 'Ư', 'V', 'X', 'Y'
                ];
                @endphp
                
                @foreach($letters as $char)

                <a href="{{ route('dictionary.index',['letter'=>$char]) }}"
                    class="{{ request('letter')==$char ? 'active' : '' }}">

                    {{ $char }}

                </a>

                @endforeach

            </div>

        </div>

        <!-- Nội dung -->

        <div class="col-lg-10">

            <div class="dictionary-header">

                <h2>
                    <i class="bi bi-book-half me-2"></i>
                    Từ điển âm thanh
                </h2>

                <form action="{{ route('dictionary.index') }}"
                    method="GET"
                    class="d-flex search-box">

                    @if(request('letter'))
                    <input type="hidden"
                        name="letter"
                        value="{{ request('letter') }}">
                    @endif

                    <input type="text"
                        name="keyword"
                        class="form-control rounded-pill me-2"
                        value="{{ $keyword }}"
                        placeholder="Tìm kiếm từ...">

                    <button class="btn btn-primary rounded-pill px-4">

                        <i class="bi bi-search"></i>

                    </button>

                </form>

            </div>

            @forelse($audios as $audio)

            <div class="word-item">

                <div class="d-flex align-items-center">

                    <div class="word-number">

                        {{ $audios->firstItem()+$loop->index }}

                    </div>

                    <div class="word-title">

                        {{ $audio['word'] }}

                    </div>

                </div>

                @if(!empty($audio['audio_url']))

                <audio controls preload="none">
                    <source src="{{ $audio['audio_url'] }}" type="audio/wav">
                    Trình duyệt không hỗ trợ phát âm thanh.
                </audio>

                @else

                <span class="text-muted">Chưa có âm thanh</span>

                @endif

            </div>

            @empty

            <div class="text-center py-5">

                <i class="bi bi-mic-fill fs-1 text-secondary mb-3"></i>

                <h5>Không có dữ liệu.</h5>

            </div>

            @endforelse

            <div class="mt-4">

                {{ $audios->links() }}

            </div>

        </div>

    </div>

</div>

@endsection