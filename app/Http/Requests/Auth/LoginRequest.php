<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules.
     */
    public function rules(): array
    {
        return [

            'role' => ['required'],

            'email' => ['nullable', 'string', 'email'],

            'employee_code' => ['nullable', 'string'],

            'password' => ['required', 'string'],

        ];
    }

    /**
     * Attempt to authenticate.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if ($this->role === 'Nhân viên') {

            $credentials = [
                'employee_code' => $this->employee_code,
                'password'      => $this->password,
                'role'          => 'Nhân viên',
            ];

            $field = 'employee_code';

        } else {

            $credentials = [
                'email'    => $this->email,
                'password' => $this->password,
                'role'     => 'Người dùng',
            ];

            $field = 'email';

        }

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                $field => trans('auth.failed'),
            ]);

        }

        // =============================
        // KIỂM TRA TÀI KHOẢN BỊ KHÓA
        // =============================

        if (Auth::user()->status === 'Khóa') {

            Auth::logout();

            throw ValidationException::withMessages([
                $field => 'Tài khoản của bạn đã bị khóa.',
            ]);

        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([

            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),

        ]);
    }

    /**
     * Get the rate limiting throttle key.
     */
    public function throttleKey(): string
    {
        $login = $this->role === 'Nhân viên'
            ? $this->employee_code
            : $this->email;

        return Str::transliterate(
            Str::lower($login).'|'.$this->ip()
        );
    }
}