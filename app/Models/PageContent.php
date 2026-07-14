<?php

namespace App\Models;

use App\Core\Model;

final class PageContent extends Model
{
    protected static string $table = 'page_content';

    public static function getSection(string $key): ?array
    {
        $row = self::whereFirst(['section_key' => $key]);

        return $row ? json_decode($row['content'], true) : null;
    }

    public static function saveSection(string $key, array $content): void
    {
        $existing = self::whereFirst(['section_key' => $key]);
        $data = ['content' => json_encode($content)];

        if ($existing) {
            self::update($existing['id'], $data);

            return;
        }

        $data['section_key'] = $key;
        self::insert($data);
    }

    public static function defaultHero(): array
    {
        return [
            'eyebrow' => 'Live 1:1 & group yoga, booked in minutes',
            'tagline' => 'Flow Like a River',
            'title' => 'Find your practice, your people, your peace.',
            'subtitle' => 'Book certified yoga instructors for live sessions tailored to your goals — from a single drop-in class to a full monthly practice.',
        ];
    }

    public static function defaultAbout(): array
    {
        return [
            'eyebrow' => 'Welcome to Vipasa Yoga',
            'heading' => 'Our Philosophy',
            'body' => "Flow Like a River — water doesn't force its way forward, it simply moves, finding its own path around every stone. That's the spirit behind every session at Vipasa: steady, unhurried, and shaped around you rather than a rigid script. Whether you're arriving to build strength or to simply breathe easier, the practice meets you exactly where you are.",
        ];
    }

    public static function defaultContact(): array
    {
        return [
            'email' => 'vipasayoga@gmail.com',
            'phone' => '+91 70127 75990',
            'location' => 'Live online sessions, worldwide',
        ];
    }

    public static function defaultTestimonials(): array
    {
        return [
            ['quote' => 'Vipasa made it so easy to build a consistent practice around my work schedule. Booking a session takes less than a minute.', 'name' => 'Meera Kapoor', 'role' => 'Member since 2025', 'photo' => '/assets/img/testimonials/meera.webp'],
            ['quote' => 'My instructor adjusted every session to what my body needed that day. It never feels like a generic class.', 'name' => 'Daniel Osei', 'role' => 'Weekly Package member', 'photo' => '/assets/img/testimonials/daniel.webp'],
            ['quote' => 'The booking flow, the reminders, the invoices — everything just works. It feels like a premium product, not a hobby project.', 'name' => 'Priya Nair', 'role' => 'Monthly Package member', 'photo' => '/assets/img/testimonials/priya.webp'],
        ];
    }

    public static function defaultFaqs(): array
    {
        return [
            ['q' => 'Do I need prior yoga experience to join?', 'a' => "Not at all. Every instructor tailors the session to your level, whether it's your first class or your thousandth."],
            ['q' => 'What do I need for a session?', 'a' => 'Just a mat, comfortable clothing, and a quiet space with a stable internet connection. Your instructor will share a Google Meet or Zoom link before your session starts.'],
            ['q' => 'Can I reschedule or cancel a booking?', 'a' => "Yes — reach out from your dashboard before your session and we'll help you find a new time that works."],
            ['q' => 'How do payments work?', 'a' => "Bookings are paid securely through our payment partner, supporting UPI, cards, netbanking, and wallets. You'll get an invoice immediately after payment."],
            ['q' => 'Can I choose my own instructor?', 'a' => 'Absolutely. Browse instructor profiles, read reviews, and pick whoever feels like the right fit before you book a time slot.'],
        ];
    }

    public static function defaultPrograms(): array
    {
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
