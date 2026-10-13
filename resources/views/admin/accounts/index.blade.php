@extends('layouts.admin')

@section('title', 'Quản lý tài khoản')

@section('content')

@if(session('success'))

<div class="alert alert-success alert-dismissible fade show">

    {{ session('success') }}

    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="alert">
    </button>

</div>

@endif

<div class="card-custom">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h4 class="mb-0 fw-bold">
            Danh sách tài khoản
        </h4>

        <form action="{{ route('admin.accounts.index') }}" method="GET" class="d-flex">

            <input
                type="text"
                name="keyword"
                value="{{ $keyword ?? '' }}"
                class="form-control me-2"
                placeholder="Tìm kiếm...">

            <button class="btn btn-primary">

                <i class="bi bi-search"></i>

            </button>

        </form>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead class="table-primary">

                <tr>

                    <th width="70">#</th>

                    <th>Họ tên</th>

                    <th>Email</th>

                    <th>Mã nhân viên</th>

                    <th>Vai trò</th>

                    <th>Trạng thái</th>

                    <th>Ngày tạo</th>

                    <th width="170" class="text-center">
                        Thao tác
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($accounts as $account)

                <tr>

                    <td>{{ $account->id }}</td>

                    <td>{{ $account->name }}</td>

                    <td>{{ $account->email }}</td>

                    <td>{{ $account->employee_code ?? '-' }}</td>

                    <td>

                        @if($account->role == 'Nhân viên')

                            <span class="badge bg-primary">
                                Nhân viên
                            </span>

                        @else

                            <span class="badge bg-success">
                                Người dùng
                            </span>

                        @endif

                    </td>

                    <td>

                        @if($account->status == 'Khóa')

                            <span class="badge bg-danger">
                                Đã khóa
                            </span>

                        @else

                            <span class="badge bg-success">
                                Hoạt động
                            </span>

                        @endif

                    </td>

                    <td>{{ $account->created_at->format('d/m/Y') }}</td>

                    <td class="text-center">

                        <a
                            href="{{ route('admin.accounts.edit', $account) }}"
                            class="btn btn-warning btn-sm">

                            <i class="bi bi-pencil-square"></i>

                        </a>

                        <form
                            action="{{ route('admin.accounts.toggleStatus', $account) }}"
                            method="POST"
                            class="d-inline">

                            @csrf
                            @method('PATCH')

                            @if($account->status == 'Hoạt động')

                                <button
                                    type="submit"
                                    class="btn btn-success btn-sm"
                                    onclick="return confirm('Bạn có muốn khóa tài khoản này?')">

                                    <i class="bi bi-unlock-fill"></i>

                                </button>

                            @else

                                <button
                                    type="submit"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Bạn có muốn mở khóa tài khoản này?')">

                                    <i class="bi bi-lock-fill"></i>

                                </button>

                            @endif

                        </form>

                    </td>

                </tr>

                @empty

                <tr>

                    <td colspan="8" class="text-center py-5">

                        Không có dữ liệu.

                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="mt-4">

        {{ $accounts->links() }}

    </div>

</div>

@endsection