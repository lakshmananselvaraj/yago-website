<?php

namespace App\Controllers\Trainer;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\Instructor;
use App\Models\InstructorCertificateFile;
use App\Models\InstructorGalleryImage;
use App\Models\User;

final class ProfileController extends Controller
{
    private const MAX_IMAGE_BYTES = 4 * 1024 * 1024;
    private const ALLOWED_IMAGE_MIMES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const MAX_CERT_BYTES = 6 * 1024 * 1024;
    private const ALLOWED_CERT_MIMES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];

    public function page(Request $request): void
    {
        $instructor = Instructor::hydrate(Instructor::findByUserId(Auth::id()));

        $this->view('trainer/profile', [
            'instructor' => $instructor,
            'user' => User::find(Auth::id()),
            'gallery' => InstructorGalleryImage::forInstructor((int) $instructor['id']),
            'certificateFiles' => InstructorCertificateFile::forInstructor((int) $instructor['id']),
        ], 'dashboard');
    }

    public function update(Request $request): void
    {
        $instructor = Instructor::findByUserId(Auth::id());

        $validator = $this->validate($request, [
            'name' => 'required|min:2|max:150',
            'phone' => 'phone',
            'headline' => 'max:200',
            'experience_years' => 'integer|min:0',
            'timezone' => 'required',
        ]);

        if ($validator->fails()) {
            $this->fail($validator->firstError() ?? 'Validation failed.', 422, $validator->errors());
        }

        Instructor::update((int) $instructor['id'], [
            'headline' => $request->input('headline') ?: null,
            'bio' => $request->input('bio') ?: null,
            'experience_years' => (int) $request->input('experience_years', 0),
            'certificates' => $this->toJsonList($request->input('certificates')),
            'specialties' => $this->toJsonList($request->input('specialties')),
            'timezone' => (string) $request->input('timezone', 'UTC'),
        ]);

        User::update((int) $instructor['user_id'], [
            'name' => (string) $request->input('name'),
            'phone' => $request->input('phone') ?: null,
        ]);

        ActivityLog::log(Auth::id(), 'trainer_profile_updated', 'instructor', (int) $instructor['id']);

        $this->success(null, 'Profile updated.');
    }

    public function uploadAvatar(Request $request): void
    {
        $instructor = Instructor::findByUserId(Auth::id());

        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $this->fail('Please choose a photo to upload.', 422);
        }

        $file = $_FILES['avatar'];
        $mime = mime_content_type($file['tmp_name']);

        if (!isset(self::ALLOWED_IMAGE_MIMES[$mime])) {
            $this->fail('Please upload a JPG, PNG, or WEBP image.', 422);
        }

        if ($file['size'] > self::MAX_IMAGE_BYTES) {
            $this->fail('Image must be smaller than 4MB.', 422);
        }

        $dir = dirname(__DIR__, 3) . '/public/uploads/avatars';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'instructor-' . $instructor['id'] . '-' . time() . '.' . self::ALLOWED_IMAGE_MIMES[$mime];
        $destination = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->fail('Could not save the uploaded photo. Please try again.', 500);
        }

        if (!empty($instructor['avatar_path'])) {
            $oldPath = dirname(__DIR__, 3) . '/public' . $instructor['avatar_path'];
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        $publicPath = '/uploads/avatars/' . $filename;
        Instructor::update((int) $instructor['id'], ['avatar_path' => $publicPath]);

        $this->success(['avatar_path' => $publicPath], 'Photo updated.');
    }

    public function uploadGalleryImage(Request $request): void
    {
        $instructor = Instructor::findByUserId(Auth::id());

        if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $this->fail('Please choose a photo to upload.', 422);
        }

        $file = $_FILES['image'];
        $mime = mime_content_type($file['tmp_name']);

        if (!isset(self::ALLOWED_IMAGE_MIMES[$mime])) {
            $this->fail('Please upload a JPG, PNG, or WEBP image.', 422);
        }

        if ($file['size'] > self::MAX_IMAGE_BYTES) {
            $this->fail('Image must be smaller than 4MB.', 422);
        }

        $dir = dirname(__DIR__, 3) . '/public/uploads/instructor-gallery';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'gallery-' . $instructor['id'] . '-' . time() . '-' . random_int(1000, 9999) . '.' . self::ALLOWED_IMAGE_MIMES[$mime];
        $destination = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->fail('Could not save the uploaded photo. Please try again.', 500);
        }

        $id = InstructorGalleryImage::insert([
            'instructor_id' => (int) $instructor['id'],
            'file_path' => '/uploads/instructor-gallery/' . $filename,
        ]);

        $this->success(['id' => $id, 'file_path' => '/uploads/instructor-gallery/' . $filename], 'Photo added.');
    }

    public function deleteGalleryImage(Request $request, int $id): void
    {
        $instructor = Instructor::findByUserId(Auth::id());
        $image = InstructorGalleryImage::find($id);

        if (!$image || (int) $image['instructor_id'] !== (int) $instructor['id']) {
            $this->fail('Image not found.', 404);
        }

        $path = dirname(__DIR__, 3) . '/public' . $image['file_path'];
        if (is_file($path)) {
            unlink($path);
        }

        InstructorGalleryImage::delete($id);

        $this->success(null, 'Photo removed.');
    }

    public function uploadCertificateFile(Request $request): void
    {
        $instructor = Instructor::findByUserId(Auth::id());

        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->fail('Please choose a file to upload.', 422);
        }

        $file = $_FILES['file'];
        $mime = mime_content_type($file['tmp_name']);

        if (!isset(self::ALLOWED_CERT_MIMES[$mime])) {
            $this->fail('Please upload a JPG, PNG, WEBP, or PDF file.', 422);
        }

        if ($file['size'] > self::MAX_CERT_BYTES) {
            $this->fail('File must be smaller than 6MB.', 422);
        }

        $dir = dirname(__DIR__, 3) . '/public/uploads/instructor-certificates';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'cert-' . $instructor['id'] . '-' . time() . '-' . random_int(1000, 9999) . '.' . self::ALLOWED_CERT_MIMES[$mime];
        $destination = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->fail('Could not save the uploaded file. Please try again.', 500);
        }

        $title = trim((string) $request->input('title', '')) ?: pathinfo($file['name'], PATHINFO_FILENAME);

        $id = InstructorCertificateFile::insert([
            'instructor_id' => (int) $instructor['id'],
            'title' => $title,
            'file_path' => '/uploads/instructor-certificates/' . $filename,
        ]);

        ActivityLog::log(Auth::id(), 'certificate_file_uploaded', 'instructor', (int) $instructor['id']);

        $this->success(['id' => $id], 'Certificate uploaded.');
    }

    public function deleteCertificateFile(Request $request, int $id): void
    {
        $instructor = Instructor::findByUserId(Auth::id());
        $file = InstructorCertificateFile::find($id);

        if (!$file || (int) $file['instructor_id'] !== (int) $instructor['id']) {
            $this->fail('File not found.', 404);
        }

        $path = dirname(__DIR__, 3) . '/public' . $file['file_path'];
        if (is_file($path)) {
            unlink($path);
        }

        InstructorCertificateFile::delete($id);
        ActivityLog::log(Auth::id(), 'certificate_file_deleted', 'instructor', (int) $instructor['id']);

        $this->success(null, 'Certificate removed.');
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
}
