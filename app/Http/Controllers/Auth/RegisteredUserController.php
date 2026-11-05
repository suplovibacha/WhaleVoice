<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Auth\FirebaseUserProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'idToken' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            // XÃƒÆ’Ã‚Â¡c minh Firebase ID Token
            $verifiedToken = app('firebase.auth')
                ->verifyIdToken($request->input('idToken'));

            $claims = $verifiedToken->claims();

            $uid = $claims->get('sub');
            $tokenEmail = $claims->get('email');

            if (
                !$uid ||
                !$tokenEmail ||
                strtolower($tokenEmail) !== strtolower($request->email)
            ) {
                return response()->json([
                    'message' => 'ThÃƒÆ’Ã‚Â´ng tin tÃƒÆ’Ã‚Â i khoÃƒÂ¡Ã‚ÂºÃ‚Â£n khÃƒÆ’Ã‚Â´ng hÃƒÂ¡Ã‚Â»Ã‚Â£p lÃƒÂ¡Ã‚Â»Ã¢â‚¬Â¡.',
                ], 401);
            }

            $firestore = app(\App\Services\FirestoreService::class);

            // KiÃƒÂ¡Ã‚Â»Ã†â€™m tra hÃƒÂ¡Ã‚Â»Ã¢â‚¬Å“ sÃƒâ€ Ã‚Â¡ Ãƒâ€žÃ¢â‚¬ËœÃƒÆ’Ã‚Â£ tÃƒÂ¡Ã‚Â»Ã¢â‚¬Å“n tÃƒÂ¡Ã‚ÂºÃ‚Â¡i hay chÃƒâ€ Ã‚Â°a
            $existingProfile = $firestore->getDocumentOrNull(
                'users',
                $uid
            );

            if ($existingProfile !== null) {
                return response()->json([
                    'message' => 'TÃƒÆ’Ã‚Â i khoÃƒÂ¡Ã‚ÂºÃ‚Â£n Ãƒâ€žÃ¢â‚¬ËœÃƒÆ’Ã‚Â£ cÃƒÆ’Ã‚Â³ hÃƒÂ¡Ã‚Â»Ã¢â‚¬Å“ sÃƒâ€ Ã‚Â¡ trong hÃƒÂ¡Ã‚Â»Ã¢â‚¬Â¡ thÃƒÂ¡Ã‚Â»Ã¢â‚¬Ëœng.',
                ], 409);
            }

            // TÃƒÂ¡Ã‚ÂºÃ‚Â¡o hÃƒÂ¡Ã‚Â»Ã¢â‚¬Å“ sÃƒâ€ Ã‚Â¡ vÃƒÂ¡Ã‚Â»Ã¢â‚¬Âºi quyÃƒÂ¡Ã‚Â»Ã‚Ân mÃƒÂ¡Ã‚ÂºÃ‚Â·c Ãƒâ€žÃ¢â‚¬ËœÃƒÂ¡Ã‚Â»Ã¢â‚¬Â¹nh
            $firestore->createUserProfile(
                $uid,
                $request->name,
                $tokenEmail
            );

            // Ãƒâ€žÃ‚ÂÃƒâ€žÃ†â€™ng nhÃƒÂ¡Ã‚ÂºÃ‚Â­p Laravel Session
            $user = app(FirebaseUserProvider::class)
                ->retrieveById($uid);

            if (!$user || $user->status !== 'active') {
                return response()->json([
                    'message' => 'KhÃƒÆ’Ã‚Â´ng thÃƒÂ¡Ã‚Â»Ã†â€™ tÃƒÂ¡Ã‚ÂºÃ‚Â¡o phiÃƒÆ’Ã‚Âªn Ãƒâ€žÃ¢â‚¬ËœÃƒâ€žÃ†â€™ng nhÃƒÂ¡Ã‚ÂºÃ‚Â­p.',
                ], 403);
            }

            Auth::guard('web')->login($user, false);

            $request->session()->regenerate();

            return response()->json([
                'message' => 'Ãƒâ€žÃ‚ÂÃƒâ€žÃ†â€™ng kÃƒÆ’Ã‚Â½ thÃƒÆ’Ã‚Â nh cÃƒÆ’Ã‚Â´ng.',
                'redirect' => route('home'),
            ]);

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'KhÃƒÆ’Ã‚Â´ng thÃƒÂ¡Ã‚Â»Ã†â€™ Ãƒâ€žÃ¢â‚¬ËœÃƒâ€žÃ†â€™ng kÃƒÆ’Ã‚Â½ tÃƒÆ’Ã‚Â i khoÃƒÂ¡Ã‚ÂºÃ‚Â£n. Vui lÃƒÆ’Ã‚Â²ng thÃƒÂ¡Ã‚Â»Ã‚Â­ lÃƒÂ¡Ã‚ÂºÃ‚Â¡i.',
            ], 500);
        }
    }
}
