<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\GalleryImage;

final class GalleryController extends Controller
{
    private const MAX_IMAGE_BYTES = 6 * 1024 * 1024;
    private const ALLOWED_IMAGE_MIMES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public function index(Request $request): void
    {
        $this->view('admin/gallery', ['images' => GalleryImage::adminAllOrdered()], 'dashboard');
    }

    public function upload(Request $request): void
    {
        if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $this->fail('Please choose a photo to upload.', 422);
        }

        $file = $_FILES['image'];
        $mime = mime_content_type($file['tmp_name']);

        if (!isset(self::ALLOWED_IMAGE_MIMES[$mime])) {
            $this->fail('Please upload a JPG, PNG, or WEBP image.', 422);
        }

        if ($file['size'] > self::MAX_IMAGE_BYTES) {
            $this->fail('Image must be smaller than 6MB.', 422);
        }

        $dir = dirname(__DIR__, 3) . '/public/assets/img/gallery';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'gallery-' . time() . '-' . random_int(1000, 9999) . '.' . self::ALLOWED_IMAGE_MIMES[$mime];
        $destination = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->fail('Could not save the uploaded photo. Please try again.', 500);
        }

        $id = GalleryImage::insert([
            'file_path' => '/assets/img/gallery/' . $filename,
            'caption' => trim((string) $request->input('caption', '')) ?: null,
            'category' => trim((string) $request->input('category', '')) ?: null,
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        ActivityLog::log(Auth::id(), 'gallery_image_uploaded', 'gallery_image', $id);

        $this->success(['id' => $id], 'Photo uploaded.');
    }

    public function update(Request $request, int $id): void
    {
        $image = GalleryImage::find($id);

        if (!$image) {
            $this->fail('Image not found.', 404);
        }

        GalleryImage::update($id, [
            'caption' => trim((string) $request->input('caption', '')) ?: null,
            'category' => trim((string) $request->input('category', '')) ?: null,
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->input('is_active', true) ? 1 : 0,
        ]);

        ActivityLog::log(Auth::id(), 'gallery_image_updated', 'gallery_image', $id);

        $this->success(null, 'Photo updated.');
    }

    public function delete(Request $request, int $id): void
    {
        $image = GalleryImage::find($id);

        if (!$image) {
            $this->fail('Image not found.', 404);
        }

        $path = dirname(__DIR__, 3) . '/public' . $image['file_path'];
        if (is_file($path) && str_starts_with($image['file_path'], '/assets/img/gallery/')) {
            unlink($path);
        }

        GalleryImage::delete($id);
        ActivityLog::log(Auth::id(), 'gallery_image_deleted', 'gallery_image', $id);

        $this->success(null, 'Photo removed.');
    }
}
