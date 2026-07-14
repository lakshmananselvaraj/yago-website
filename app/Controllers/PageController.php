<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\GalleryImage;
use App\Models\Instructor;
use App\Models\Package;
use App\Models\PageContent;

final class PageController extends Controller
{
    public function splash(Request $request): void
    {
        if (Auth::check()) {
            $this->view('splash', ['redirectTo' => Auth::redirectHome()], 'bare');
            return;
        }

        $instructors = Instructor::searchActive();
        usort($instructors, static fn ($a, $b) => (float) $b['rating_avg'] <=> (float) $a['rating_avg']);

        // Real, honest numbers only — no padded/fabricated vanity metrics for a
        // freshly-launched platform. These stay true even before real traction.
        $db = Database::connection();
        $instructorCount = (int) $db->query("SELECT COUNT(*) c FROM instructors WHERE status = 'active'")->fetch()['c'];
        $combinedYears = (int) $db->query("SELECT COALESCE(SUM(experience_years), 0) y FROM instructors WHERE status = 'active'")->fetch()['y'];
        $programCount = (int) $db->query("SELECT COUNT(*) c FROM packages WHERE is_active = 1")->fetch()['c'];
        $avgRatingRow = $db->query("SELECT AVG(rating_avg) r FROM instructors WHERE rating_count > 0 AND status = 'active'")->fetch();
        $avgRating = $avgRatingRow['r'] !== null ? round((float) $avgRatingRow['r'], 1) : null;

        $this->view('landing', [
            'packages' => Package::featured(),
            'programs' => $this->programs(),
            'instructors' => array_slice($instructors, 0, 3),
            'stats' => [
                'instructors' => $instructorCount,
                'years' => $combinedYears,
                'programs' => $programCount,
                'rating' => $avgRating,
            ],
            'hero' => PageContent::getSection('hero') ?? PageContent::defaultHero(),
            'about' => PageContent::getSection('about') ?? PageContent::defaultAbout(),
            'testimonials' => PageContent::getSection('testimonials') ?? PageContent::defaultTestimonials(),
            'faqs' => PageContent::getSection('faqs') ?? PageContent::defaultFaqs(),
            'contact' => PageContent::getSection('contact') ?? PageContent::defaultContact(),
        ], 'bare');
    }

    public function gallery(Request $request): void
    {
        $images = GalleryImage::allOrdered();

        if (empty($images)) {
            $images = $this->defaultGalleryImages();
        } else {
            foreach ($images as &$image) {
                $image['path'] = $image['file_path'];
            }
            unset($image);
        }

        $this->view('gallery', ['photos' => $images], 'bare');
    }

    private function defaultGalleryImages(): array
    {
        $photos = [
            ['file' => 'pose-plank-fold.webp', 'caption' => 'Plank to forward fold', 'category' => 'poses'],
            ['file' => 'pose-shoulderstand.webp', 'caption' => 'Shoulder stand preparation', 'category' => 'inversions'],
            ['file' => 'pose-side-stretch.webp', 'caption' => 'Seated side stretch', 'category' => 'poses'],
            ['file' => 'pose-crow-1.webp', 'caption' => 'Crow pose (Bakasana)', 'category' => 'poses'],
            ['file' => 'pose-crow-3.webp', 'caption' => 'Crow pose, close focus', 'category' => 'poses'],
            ['file' => 'pose-plough.webp', 'caption' => 'Plough pose', 'category' => 'inversions'],
            ['file' => 'pose-headstand-1.webp', 'caption' => 'Headstand practice', 'category' => 'inversions'],
            ['file' => 'pose-headstand-2.webp', 'caption' => 'Headstand, full room view', 'category' => 'inversions'],
            ['file' => 'pose-bridge.webp', 'caption' => 'Bridge pose', 'category' => 'poses'],
            ['file' => 'pose-shoulderstand-2.webp', 'caption' => 'Shoulder stand, studio corner', 'category' => 'inversions'],
            ['file' => 'pose-boat-balcony-2.webp', 'caption' => 'Boat pose, balcony view', 'category' => 'poses'],
            ['file' => 'pose-plough-2.webp', 'caption' => 'Plough pose variation', 'category' => 'inversions'],
            ['file' => 'about-journey.webp', 'caption' => 'A quiet moment before practice', 'category' => 'stillness'],
            ['file' => 'philosophy-meditation.webp', 'caption' => 'Seated meditation', 'category' => 'stillness'],
        ];

        foreach ($photos as &$photo) {
            $photo['path'] = '/assets/img/client/' . $photo['file'];
        }

        return $photos;
    }

    /**
     * Studio program offerings — marketing content, deliberately separate from
     * the service_types/packages tables that drive real booking (those model
     * session *format*: one-to-one/group/weekly/monthly; these are yoga
     * *styles*). Real client photography is used wherever it exists; a small
     * stock fallback covers styles this instructor hasn't been photographed
     * teaching yet (prenatal/postnatal/preconception/face/kids yoga).
     */
    private function programs(): array
    {
        $stored = PageContent::getSection('programs');

        if (!empty($stored)) {
            return $stored;
        }

        return [
            ['name' => 'Regular Yoga', 'img' => '/assets/img/client/pose-plank-fold.webp', 'description' => 'A well-rounded practice combining strength, flexibility and breath — the foundation for everything else.'],
            ['name' => 'Yoga Therapy', 'img' => '/assets/img/client/pose-childs-pose-extended.webp', 'description' => 'Gentle, therapeutic sequences shaped around specific injuries, conditions, or recovery goals.'],
            ['name' => 'Preconception Yoga', 'img' => '/assets/img/programs/preconception-yoga.webp', 'description' => 'Calming, fertility-supportive practice for those preparing for the journey ahead.'],
            ['name' => 'Prenatal Yoga', 'img' => '/assets/img/programs/prenatal-yoga.webp', 'description' => 'Safe, trimester-aware sequences that keep you strong and comfortable through pregnancy.'],
            ['name' => 'Postnatal Yoga', 'img' => '/assets/img/programs/postnatal-yoga.webp', 'description' => 'Gentle, recovery-focused practice to rebuild strength and ease back into movement after birth.'],
            ['name' => "Women's Wellness", 'img' => '/assets/img/client/philosophy-flow.webp', 'description' => "Practice tailored to the rhythms of a woman's body — hormonal balance, energy, and stress relief."],
            ['name' => 'Mat Pilates', 'img' => '/assets/img/client/pose-boat-balcony.webp', 'description' => 'Core-focused, controlled movement on the mat — building real strength with no special equipment.'],
            ['name' => 'Face Yoga', 'img' => '/assets/img/programs/face-yoga.webp', 'description' => 'Gentle facial exercises and massage techniques to relax tension and support natural radiance.'],
            ['name' => 'Kids Yoga', 'img' => '/assets/img/programs/kids-yoga.webp', 'description' => 'Playful, imaginative sessions that introduce children to movement, focus, and calm.'],
            ['name' => 'Private Session', 'img' => '/assets/img/client/pose-dramatic-stretch.webp', 'description' => 'One-on-one attention, fully tailored to your goals, pace, and comfort level.'],
        ];
    }
}
