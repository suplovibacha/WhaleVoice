
import Alpine from 'alpinejs';
import { auth } from './firebase';

import {
    signInWithEmailAndPassword,
    createUserWithEmailAndPassword,
    updateProfile,
    signOut,
    getIdToken
} from 'firebase/auth';

window.Alpine = Alpine;
Alpine.start();

// =============================
// SEND TOKEN TO LARAVEL
// =============================

async function sendTokenToLaravel(form, payload) {
    const response = await fetch(form.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': form.querySelector(
                'input[name="_token"]'
            ).value
        },
        body: JSON.stringify(payload)
    });

    const data = await response.json();

    if (!response.ok) {
        throw new Error(data.message || 'Không thể xử lý yêu cầu.');
    }

    return data;
}

// =============================
// FIREBASE LOGIN
// =============================

const loginForm = document.getElementById('loginForm');

if (loginForm) {
    loginForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const errorBox = document.getElementById('loginError');
        const button = loginForm.querySelector('button[type="submit"]');

        errorBox.style.display = 'none';
        errorBox.textContent = '';

        button.disabled = true;
        button.textContent = 'Đang đăng nhập...';

        try {
            const credential = await signInWithEmailAndPassword(
                auth,
                email,
                password
            );

            const idToken = await getIdToken(credential.user);

            const data = await sendTokenToLaravel(loginForm, {
                email: email,
                idToken: idToken
            });

            window.location.href = data.redirect;

        } catch (error) {
            await signOut(auth).catch(() => {});

            let message = error.message;

            if (error.code === 'auth/invalid-credential') {
                message = 'Email hoặc mật khẩu không chính xác.';
            } else if (error.code === 'auth/too-many-requests') {
                message = 'Bạn đã thử đăng nhập quá nhiều lần. Vui lòng thử lại sau.';
            }

            errorBox.textContent = message;
            errorBox.style.display = 'block';

            button.disabled = false;
            button.innerHTML =
                '<i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập';
        }
    });
}

// =============================
// FIREBASE REGISTER
// =============================

const registerForm = document.getElementById('registerForm');

if (registerForm) {
    registerForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const passwordConfirmation =
            document.getElementById('password_confirmation').value;

        const errorBox = document.getElementById('registerError');
        const button = registerForm.querySelector('button[type="submit"]');

        errorBox.style.display = 'none';
        errorBox.textContent = '';

        if (password !== passwordConfirmation) {
            errorBox.textContent = 'Mật khẩu xác nhận không khớp.';
            errorBox.style.display = 'block';
            return;
        }

        button.disabled = true;
        button.textContent = 'Đang đăng ký...';

        try {
            // 1. Tao tai khoan Firebase Authentication
            const credential = await createUserWithEmailAndPassword(
                auth,
                email,
                password
            );

            // 2. Cap nhat ten hien thi Firebase
            await updateProfile(credential.user, {
                displayName: name
            });

            // 3. Lay ID Token
            const idToken = await getIdToken(credential.user);

            // 4. Gui thong tin ve Laravel de tao ho so Firestore
            const data = await sendTokenToLaravel(registerForm, {
                name: name,
                email: email,
                idToken: idToken
            });

            // 5. Chuyen trang sau khi Laravel tao Session
            window.location.href = data.redirect;

        } catch (error) {
            await signOut(auth).catch(() => {});

            let message = error.message;

            if (error.code === 'auth/email-already-in-use') {
                message = 'Email này đã được sử dụng.';
            } else if (error.code === 'auth/weak-password') {
                message = 'Mật khẩu phải có ít nhất 6 ký tự.';
            } else if (error.code === 'auth/invalid-email') {
                message = 'Email không hợp lệ.';
            }

            errorBox.textContent = message;
            errorBox.style.display = 'block';

            button.disabled = false;
            button.innerHTML =
                '<i class="bi bi-person-plus-fill me-2"></i>Đăng ký';
        }
    });
}
