<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\PageContent;

final class ContentController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('admin/content', [
            'hero' => PageContent::getSection('hero') ?? PageContent::defaultHero(),
            'about' => PageContent::getSection('about') ?? PageContent::defaultAbout(),
            'programs' => PageContent::getSection('programs') ?? PageContent::defaultPrograms(),
            'testimonials' => PageContent::getSection('testimonials') ?? PageContent::defaultTestimonials(),
            'faqs' => PageContent::getSection('faqs') ?? PageContent::defaultFaqs(),
            'contact' => PageContent::getSection('contact') ?? PageContent::defaultContact(),
        ], 'dashboard');
    }

    public function updateHero(Request $request): void
    {
        PageContent::saveSection('hero', [
            'eyebrow' => trim((string) $request->input('eyebrow', '')),
            'tagline' => trim((string) $request->input('tagline', '')),
            'title' => trim((string) $request->input('title', '')),
            'subtitle' => trim((string) $request->input('subtitle', '')),
        ]);

        ActivityLog::log(Auth::id(), 'content_updated', 'page_content', null, ['section' => 'hero']);
        $this->success(null, 'Hero section updated.');
    }

    public function updateAbout(Request $request): void
    {
        PageContent::saveSection('about', [
            'eyebrow' => trim((string) $request->input('eyebrow', '')),
            'heading' => trim((string) $request->input('heading', '')),
            'body' => trim((string) $request->input('body', '')),
        ]);

        ActivityLog::log(Auth::id(), 'content_updated', 'page_content', null, ['section' => 'about']);
        $this->success(null, 'About section updated.');
    }

    public function updateContact(Request $request): void
    {
        PageContent::saveSection('contact', [
            'email' => trim((string) $request->input('email', '')),
            'phone' => trim((string) $request->input('phone', '')),
            'location' => trim((string) $request->input('location', '')),
        ]);

        ActivityLog::log(Auth::id(), 'content_updated', 'page_content', null, ['section' => 'contact']);
        $this->success(null, 'Contact details updated.');
    }

    public function updateTestimonials(Request $request): void
    {
        $items = $this->sanitizeItems($request->input('items', []), ['quote', 'name', 'role', 'photo']);
        PageContent::saveSection('testimonials', $items);

        ActivityLog::log(Auth::id(), 'content_updated', 'page_content', null, ['section' => 'testimonials']);
        $this->success(null, 'Testimonials updated.');
    }

    public function updateFaqs(Request $request): void
    {
        $items = $this->sanitizeItems($request->input('items', []), ['q', 'a']);
        PageContent::saveSection('faqs', $items);

        ActivityLog::log(Auth::id(), 'content_updated', 'page_content', null, ['section' => 'faqs']);
        $this->success(null, 'FAQs updated.');
    }

    public function updatePrograms(Request $request): void
    {
        $items = $this->sanitizeItems($request->input('items', []), ['name', 'description', 'img']);
        PageContent::saveSection('programs', $items);

        ActivityLog::log(Auth::id(), 'content_updated', 'page_content', null, ['section' => 'programs']);
        $this->success(null, 'Programs updated.');
    }

    private function sanitizeItems(mixed $items, array $fields): array
    {
        if (!is_array($items)) {
            return [];
        }

        $clean = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $row = [];
            foreach ($fields as $field) {
                $row[$field] = trim((string) ($item[$field] ?? ''));
            }

            if (implode('', $row) === '') {
                continue;
            }

            $clean[] = $row;
        }

        return $clean;
    }
}
