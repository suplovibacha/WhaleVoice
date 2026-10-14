@extends('layouts.admin')

@section('title', 'Quản lý từ điển âm thanh')

@section('content')

<div class="card-custom">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h4 class="mb-0 fw-bold">
            Danh sách từ điển âm thanh
        </h4>

        <div class="d-flex">

            <form action="{{ route('admin.audio-dictionary.index') }}" method="GET" class="d-flex me-2">

                <input
                    type="text"
                    name="keyword"
                    value="{{ $keyword ?? '' }}"
                    class="form-control me-2"
                    placeholder="Tìm từ...">

                <button class="btn btn-primary">
                    <i class="bi bi-search"></i>
                </button>

            </form>

            <a href="{{ route('admin.audio-dictionary.create') }}"
               class="btn btn-success">

                <i class="bi bi-plus-circle me-1"></i>

                Thêm từ

            </a>

        </div>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead class="table-primary">

                <tr>

                    <th width="70">#</th>

                    <th>Từ</th>

                    <th width="250">Âm thanh</th>

                    <th>Đường dẫn</th>

                    <th width="120">Trạng thái</th>

                    <th width="120">Ngày tạo</th>

                    <th width="220" class="text-center">
                        Thao tác
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($audios as $audio)

                <tr>

                    <td>{{ $audios->firstItem() + $loop->index }}</td>

                    <td>{{ $audio->word }}</td>

                    <td>

                        <audio controls preload="none" style="width:220px">

                            <source src="{{ asset('storage/'.$audio->audio) }}" type="audio/mpeg">

                            Trình duyệt của bạn không hỗ trợ phát âm thanh.

                        </audio>

                    </td>

                    <td>

                        <small class="text-muted">

                            {{ basename($audio->audio) }}

                        </small>

                    </td>

                    <td>

                        @if($audio->status == 'Khóa')

                            <span class="badge bg-danger">
                                Đã khóa
                            </span>

                        @else

                            <span class="badge bg-success">
                                Hoạt động
                            </span>

                        @endif

                    </td>

                    <td>

                        {{ $audio->created_at->format('d/m/Y') }}

                    </td>

                    <td class="text-center">

                        <!-- Chỉnh sửa -->

                        <a href="{{ route('admin.audio-dictionary.edit',$audio->id) }}"
                           class="btn btn-warning btn-sm"
                           title="Chỉnh sửa">

                            <i class="bi bi-pencil-square"></i>

                        </a>

                        <!-- Khóa / Mở khóa -->

                        <form action="{{ route('admin.audio-dictionary.toggle-status',$audio->id) }}"
                              method="POST"
                              class="d-inline">

                            @csrf

                            @if($audio->status == 'Khóa')

                                <button
                                    type="submit"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Mở khóa từ này?')">

                                    <i class="bi bi-lock-fill"></i>

                                </button>

                            @else

                                <button
                                    type="submit"
                                    class="btn btn-success btn-sm"
                                    onclick="return confirm('Khóa từ này?')">

                                    <i class="bi bi-unlock-fill"></i>

                                </button>

                            @endif

                        </form>

                        <!-- Xóa -->

                        <form action="{{ route('admin.audio-dictionary.destroy',$audio->id) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Bạn có chắc muốn xóa từ này?')">

                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="btn btn-danger btn-sm">

                                <i class="bi bi-trash-fill"></i>

                            </button>

                        </form>

                    </td>

                </tr>

                @empty

                <tr>

                    <td colspan="7" class="text-center py-5">

                        <i class="bi bi-mic fs-1 text-secondary d-block mb-3"></i>

                        Chưa có dữ liệu từ điển âm thanh.

                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="mt-4">

        {{ $audios->links() }}

    </div>

</div>

@endsection