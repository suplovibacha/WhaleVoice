<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): JsonResponse
    {
        $idToken = $request->input('idToken');

        // 1. Xác minh Firebase ID Token
        try {
            $verifiedToken = app('firebase.auth')
                ->verifyIdToken($idToken);

            $claims = $verifiedToken->claims();

            $uid = $claims->get('sub');
            $tokenEmail = $claims->get('email');
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Phiên đăng nhập Firebase không hợp lệ. Vui lòng đăng nhập lại.',
            ], 401);
        }

        // 2. Kiểm tra email
        if (
            !$uid ||
            !$tokenEmail ||
            strtolower($tokenEmail) !== strtolower($request->email)
        ) {
            return response()->json([
                'message' => 'Thông tin tài khoản không hợp lệ.',
            ], 401);
        }

        // 3. Đọc hồ sơ từ Firestore
        $user = app(\App\Auth\FirebaseUserProvider::class)
            ->retrieveById($uid);

        if (!$user) {
            return response()->json([
                'message' => 'Tài khoản chưa có hồ sơ trong hệ thống.',
            ], 403);
        }

        // 4. Kiểm tra trạng thái tài khoản
        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Tài khoản của bạn đã bị khóa.',
            ], 403);
        }

        // 5. Tạo Laravel Session
        Auth::guard('web')->login($user, false);

        $request->session()->regenerate();

        // 6. Điều hướng theo quyền
        $redirect = in_array($user->role, ['employee', 'admin'], true)
            ? route('admin.dashboard')
            : route('home');

        return response()->json([
            'message' => 'Đăng nhập thành công.',
            'redirect' => $redirect,
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
