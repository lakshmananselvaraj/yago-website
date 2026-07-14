<?php

namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\ClientProfile;
use App\Models\User;

final class ProfileController extends Controller
{
    private const GENDERS = ['female', 'male', 'non_binary', 'prefer_not_to_say'];
    private const MAX_AVATAR_BYTES = 2 * 1024 * 1024;
    private const ALLOWED_AVATAR_MIMES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public function show(Request $request): void
    {
        $user = User::find(Auth::id());
        $profile = ClientProfile::findByUserId(Auth::id());

        $this->success([
            'user' => ['name' => $user['name'], 'email' => $user['email'], 'phone' => $user['phone']],
            'profile' => $profile ? ClientProfile::hydrate($profile) : null,
            'google_linked' => !empty($user['google_id']),
        ]);
    }

    public function update(Request $request): void
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:150',
            'phone' => 'phone',
            'age' => 'integer',
            'gender' => 'in:' . implode(',', self::GENDERS),
            'country' => 'max:100',
            'bio' => 'max:200',
            'timezone' => 'required|max:60',
            'medical_notes' => 'max:2000',
        ]);

        if ($validator->fails()) {
            $this->fail($validator->firstError() ?? 'Validation failed.', 422, $validator->errors());
        }

        $userId = Auth::id();

        User::update($userId, array_filter([
            'name' => $request->input('name'),
            'phone' => $request->input('phone') ?: null,
        ], static fn ($v) => $v !== null));

        $age = $request->input('age');

        $data = array_filter([
            'age' => ($age !== null && $age !== '') ? (int) $age : null,
            'gender' => $request->input('gender') ?: null,
            'country' => $request->input('country', ''),
            'bio' => $request->input('bio', ''),
            'timezone' => $request->input('timezone'),
            'medical_notes' => $request->input('medical_notes', ''),
        ], static fn ($v) => $v !== null);

        if ((bool) $request->input('complete', false)) {
            $data['onboarding_completed_at'] = date('Y-m-d H:i:s');
        }

        ClientProfile::upsertForUser($userId, $data);

        $this->success(['redirect' => '/services'], 'Profile saved.');
    }

    public function changePassword(Request $request): void
    {
        $currentPassword = (string) $request->input('current_password', '');
        $newPassword = (string) $request->input('new_password', '');

        if (strlen($newPassword) < 8) {
            $this->fail('New password must be at least 8 characters.', 422, ['new_password' => ['Too short.']]);
        }

        $user = User::find(Auth::id());

        if (!empty($user['password_hash'])) {
            if ($currentPassword === '' || !password_verify($currentPassword, $user['password_hash'])) {
                $this->fail('Current password is incorrect.', 422, ['current_password' => ['Incorrect password.']]);
            }
        }

        User::setPassword(Auth::id(), $newPassword);

        $this->success(null, 'Password updated.');
    }

    public function uploadAvatar(Request $request): void
    {
        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $this->fail('Please choose a photo to upload.', 422);
        }

        $file = $_FILES['avatar'];
        $mime = mime_content_type($file['tmp_name']);

        if (!isset(self::ALLOWED_AVATAR_MIMES[$mime])) {
            $this->fail('Please upload a JPG, PNG, or WEBP image.', 422);
        }

        if ($file['size'] > self::MAX_AVATAR_BYTES) {
            $this->fail('Image must be smaller than 2MB.', 422);
        }

        $userId = Auth::id();
        $dir = dirname(__DIR__, 3) . '/public/uploads/avatars';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = $userId . '-' . time() . '.' . self::ALLOWED_AVATAR_MIMES[$mime];
        $destination = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->fail('Could not save the uploaded photo. Please try again.', 500);
        }

        $existing = ClientProfile::findByUserId($userId);
        if ($existing && !empty($existing['avatar_path'])) {
            $oldPath = dirname(__DIR__, 3) . '/public' . $existing['avatar_path'];
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        $publicPath = '/uploads/avatars/' . $filename;
        ClientProfile::upsertForUser($userId, ['avatar_path' => $publicPath]);

        $this->success(['avatar_path' => $publicPath], 'Photo updated.');
    }
}
