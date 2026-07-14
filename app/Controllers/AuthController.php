<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\GoogleOAuth;
use App\Core\Request;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\EmailVerification;
use App\Models\PasswordReset;
use App\Models\User;

final class AuthController extends Controller
{
    public function loginPage(Request $request): void
    {
        $this->view('auth/login', [], 'bare');
    }

    public function signupPage(Request $request): void
    {
        $this->view('auth/signup', [], 'bare');
    }

    public function forgotPasswordPage(Request $request): void
    {
        $this->view('auth/forgot-password', [], 'bare');
    }

    public function resetPasswordPage(Request $request): void
    {
        $token = (string) $request->query('token', '');
        $valid = $token !== '' && PasswordReset::verifyByToken($token) !== null;

        $this->view('auth/reset-password', ['token' => $token, 'valid' => $valid], 'bare');
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        $this->redirect('/login');
    }

    public function verifyEmail(Request $request): void
    {
        $token = (string) $request->query('token', '');
        $userId = $token !== '' ? EmailVerification::verifyByToken($token) : null;

        if ($userId) {
            User::markEmailVerified($userId);
        }

        $this->view('auth/verify-email-result', ['success' => $userId !== null], 'bare');
    }

    public function googleRedirect(Request $request): void
    {
        if (!GoogleOAuth::isConfigured()) {
            $this->redirect('/login?google_error=not_configured');
        }

        $redirect = (string) $request->query('redirect', '');
        if (Auth::isSafeRedirectPath($redirect)) {
            Session::set('_oauth_redirect', $redirect);
        }

        $this->redirect(GoogleOAuth::authorizationUrl());
    }

    public function googleCallback(Request $request): void
    {
        $state = $request->query('state');
        $code = $request->query('code');

        if (!GoogleOAuth::verifyState($state) || !$code) {
            $this->redirect('/login?google_error=failed');
        }

        $googleUser = GoogleOAuth::fetchUser((string) $code);

        if (!$googleUser) {
            $this->redirect('/login?google_error=failed');
        }

        $user = User::findByGoogleId($googleUser['google_id']);

        if (!$user) {
            $existingByEmail = User::findBy('email', $googleUser['email']);

            if ($existingByEmail) {
                User::linkGoogleId((int) $existingByEmail['id'], $googleUser['google_id']);
                $user = User::find((int) $existingByEmail['id']);
            } else {
                $newUserId = User::createFromGoogle($googleUser['name'], $googleUser['email'], $googleUser['google_id']);
                $user = User::find($newUserId);
            }
        }

        Auth::login($user);
        ActivityLog::log((int) $user['id'], 'login_google', 'user', (int) $user['id']);

        $redirect = Session::get('_oauth_redirect');
        Session::remove('_oauth_redirect');

        $this->redirect(Auth::isSafeRedirectPath($redirect) ? $redirect : Auth::redirectHome());
    }
}
