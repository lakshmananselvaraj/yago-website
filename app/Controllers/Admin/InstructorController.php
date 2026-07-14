<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Instructor;
use App\Models\PasswordReset;
use App\Models\User;

final class InstructorController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('admin/instructors', [
            'instructors' => Instructor::adminList(),
        ], 'dashboard');
    }

    public function store(Request $request): void
    {
        $validator = $this->validate($request, $this->rules());

        if ($validator->fails()) {
            $this->fail($validator->firstError() ?? 'Validation failed.', 422, $validator->errors());
        }

        $name = (string) $request->input('name');
        $email = (string) $request->input('email');
        $phone = $request->input('phone') ?: null;

        if (User::existsWithEmailOrPhone($email, $phone)) {
            $this->fail('An account with this email or phone already exists.', 409);
        }

        $userId = User::insert([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => null,
            'role' => 'instructor',
            'status' => 'active',
        ]);

        $instructorId = Instructor::insert([
            'user_id' => $userId,
            'headline' => $request->input('headline') ?: null,
            'bio' => $request->input('bio') ?: null,
            'experience_years' => (int) $request->input('experience_years', 0),
            'certificates' => $this->toJsonList($request->input('certificates')),
            'specialties' => $this->toJsonList($request->input('specialties')),
            'timezone' => (string) $request->input('timezone', 'UTC'),
            'status' => 'active',
        ]);

        $this->sendInviteEmail($userId, $email, $name);

        ActivityLog::log(Auth::id(), 'instructor_created', 'instructor', $instructorId, ['name' => $name]);

        $this->success(['id' => $instructorId], 'Instructor added.');
    }

    public function update(Request $request, int $id): void
    {
        $instructor = Instructor::find($id);

        if (!$instructor) {
            $this->fail('Instructor not found.', 404);
        }

        $validator = $this->validate($request, $this->rules(false));

        if ($validator->fails()) {
            $this->fail($validator->firstError() ?? 'Validation failed.', 422, $validator->errors());
        }

        $name = (string) $request->input('name');

        Instructor::update($id, [
            'headline' => $request->input('headline') ?: null,
            'bio' => $request->input('bio') ?: null,
            'experience_years' => (int) $request->input('experience_years', 0),
            'certificates' => $this->toJsonList($request->input('certificates')),
            'specialties' => $this->toJsonList($request->input('specialties')),
            'timezone' => (string) $request->input('timezone', 'UTC'),
        ]);

        User::update((int) $instructor['user_id'], [
            'name' => $name,
            'phone' => $request->input('phone') ?: null,
        ]);

        ActivityLog::log(Auth::id(), 'instructor_updated', 'instructor', $id, ['name' => $name]);

        $this->success(null, 'Instructor updated.');
    }

    public function toggleActive(Request $request, int $id): void
    {
        $instructor = Instructor::find($id);

        if (!$instructor) {
            $this->fail('Instructor not found.', 404);
        }

        $newStatus = $instructor['status'] === 'active' ? 'inactive' : 'active';
        Instructor::update($id, ['status' => $newStatus]);
        ActivityLog::log(Auth::id(), $newStatus === 'active' ? 'instructor_activated' : 'instructor_deactivated', 'instructor', $id);

        $this->success(['status' => $newStatus], $newStatus === 'active' ? 'Instructor activated.' : 'Instructor deactivated.');
    }

    private function rules(bool $requireEmail = true): array
    {
        $rules = [
            'name' => 'required|min:2|max:150',
            'phone' => 'phone',
            'headline' => 'max:200',
            'experience_years' => 'integer|min:0',
            'timezone' => 'required',
        ];

        if ($requireEmail) {
            $rules['email'] = 'required|email|max:190';
        }

        return $rules;
    }

    private function toJsonList(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $items = array_values(array_filter(
            array_map('trim', explode(',', $value)),
            static fn (string $v): bool => $v !== ''
        ));

        return !empty($items) ? json_encode($items) : null;
    }

    private function sendInviteEmail(int $userId, string $email, string $name): void
    {
        $token = PasswordReset::createToken($userId);
        $config = require dirname(__DIR__, 2) . '/Config/app.php';
        $link = $config['url'] . '/reset-password?token=' . urlencode($token);

        Mailer::send(
            $email,
            "You've been added as an instructor at Vipasa Yoga",
            "<p>Hi {$name},</p><p>You've been added as an instructor at Vipasa Yoga. Please click the link below to set your password and sign in:</p><p><a href=\"{$link}\">{$link}</a></p><p>This link expires in 30 minutes.</p>"
        );
    }
}
