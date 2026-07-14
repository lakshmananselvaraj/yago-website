<?php

namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Validator;
use App\Models\ActivityLog;
use App\Models\EmailVerification;
use App\Models\PasswordReset;
use App\Models\User;

final class AuthController extends Controller
{
    public function signup(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:150',
            'email' => 'required|email|max:190',
            'phone' => 'phone',
            'password' => 'required|min:8',
            'password_confirmation' => 'required',
        ]);

        if ($validator->fails()) {
            $this->fail($validator->firstError() ?? 'Validation failed.', 422, $validator->errors());
        }

        $email = (string) $request->input('email');
        $phone = $request->input('phone') ?: null;

        if ($request->input('password') !== $request->input('password_confirmation')) {
            $this->fail('Passwords do not match.', 422, ['password_confirmation' => ['Passwords do not match.']]);
        }

        if (User::existsWithEmailOrPhone($email, $phone)) {
            $this->fail('An account with this email or phone already exists.', 409);
        }

        $userId = User::createAccount((string) $request->input('name'), $email, $phone, (string) $request->input('password'));
        User::update($userId, ['status' => 'active']);

        $this->sendVerificationEmail($userId, $email);
        $this->sendWelcomeEmail($email, (string) $request->input('name'));
        Auth::login(User::find($userId));
        ActivityLog::log($userId, 'signup', 'user', $userId);

        $this->success(['redirect' => '/onboarding/profile'], 'Welcome to Vipasa Yoga!');
    }

    public function login(Request $request): void
    {
        $email = (string) $request->input('email', '');
        $password = (string) $request->input('password', '');

        if ($email === '' || $password === '') {
            $this->fail('Please enter your email and password.', 422);
        }

        $user = Auth::attempt($email, $password, (bool) $request->input('remember', false));

        if (!$user) {
            $this->fail('Incorrect credentials. Please try again.', 401);
        }

        if ($user['status'] === 'suspended') {
            Auth::logout();
            $this->fail('This account has been suspended. Please contact support.', 403);
        }

        ActivityLog::log((int) $user['id'], 'login', 'user', (int) $user['id']);

        $redirect = (string) $request->input('redirect', '');
        $destination = Auth::isSafeRedirectPath($redirect) ? $redirect : Auth::redirectHome();

        $this->success(['redirect' => $destination], 'Welcome back!');
    }

    private function sendVerificationEmail(int $userId, string $email): void
    {
        $token = EmailVerification::createToken($userId);
        $config = require dirname(__DIR__, 2) . '/Config/app.php';
        $link = $config['url'] . '/verify-email?token=' . urlencode($token);

        Mailer::send($email, 'Verify your Vipasa Yoga email', "<p>Please confirm your email address:</p><p><a href=\"{$link}\">{$link}</a></p><p>This link expires in 24 hours.</p>");
    }

    private function sendWelcomeEmail(string $email, string $name): void
    {
        $config = require dirname(__DIR__, 2) . '/Config/app.php';
        $safeName = htmlspecialchars($name, ENT_QUOTES);

        Mailer::send($email, 'Welcome to Vipasa Yoga', <<<HTML
            <p>Hi {$safeName},</p>
            <p>Welcome to Vipasa Yoga! Your account is ready — browse our packages and book your first session whenever you're ready.</p>
            <p><a href="{$config['url']}/services">Browse services</a></p>
            HTML);
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        $this->success(null, 'Logged out.');
    }

    public function forgotPassword(Request $request): void
    {
        $email = (string) $request->input('email', '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->fail('Please enter a valid email address.', 422);
        }

        $user = User::findBy('email', $email);

        if ($user) {
            $this->sendPasswordResetEmail((int) $user['id'], $email);
        }

        // Deliberately identical response whether or not the account exists, so this
        // endpoint can't be used to enumerate registered emails.
        $this->success(null, 'If an account exists for that email, a password reset link has been sent.');
    }

    private function sendPasswordResetEmail(int $userId, string $email): void
    {
        $token = PasswordReset::createToken($userId);
        $config = require dirname(__DIR__, 2) . '/Config/app.php';
        $link = $config['url'] . '/reset-password?token=' . urlencode($token);

        Mailer::send($email, 'Reset your Vipasa Yoga password', "<p>We received a request to reset your password.</p><p><a href=\"{$link}\">{$link}</a></p><p>This link expires in 30 minutes. If you didn't request this, you can safely ignore this email.</p>");
    }

    public function resetPassword(Request $request): void
    {
        $token = (string) $request->input('token', '');
        $userId = $token !== '' ? PasswordReset::verifyByToken($token) : null;

        if (!$userId) {
            $this->fail('This reset link is invalid or has expired. Please request a new one.', 419);
        }

        $password = (string) $request->input('password', '');
        if (strlen($password) < 8) {
            $this->fail('Password must be at least 8 characters.', 422, ['password' => ['Too short.']]);
        }

        if ($password !== $request->input('password_confirmation')) {
            $this->fail('Passwords do not match.', 422, ['password_confirmation' => ['Passwords do not match.']]);
        }

        if (!PasswordReset::consume($token)) {
            $this->fail('This reset link is invalid or has expired. Please request a new one.', 419);
        }

        User::setPassword($userId, $password);
        ActivityLog::log($userId, 'password_reset', 'user', $userId);

        $this->success(['redirect' => '/login'], 'Password updated. Please sign in.');
    }
}
