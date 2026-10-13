<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Hiển thị danh sách tài khoản.
     */
    public function index()
    {
        $accounts = User::orderBy('created_at', 'asc')->paginate(10);

        return view('admin.accounts.index', compact('accounts'));
    }

    /**
     * Hiển thị form thêm tài khoản.
     */
    public function create()
    {
        return view('admin.accounts.create');
    }

    /**
     * Lưu tài khoản mới.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Hiển thị chi tiết tài khoản.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Hiển thị form sửa tài khoản.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Cập nhật tài khoản.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Xóa tài khoản.
     */
    public function destroy(string $id)
    {
        //
    }
    public function toggleStatus(User $account)
    {
        $account->status = $account->status == 'Hoạt động'
            ? 'Khóa'
            : 'Hoạt động';

        $account->save();

        return back()->with('success', 'Cập nhật trạng thái thành công.');
    }
}