<?php

namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Models\BookingSlot;
use App\Models\Favorite;
use App\Models\Instructor;
use App\Models\InstructorAvailability;
use App\Models\InstructorBlockedDate;
use App\Models\InstructorReview;
use App\Models\InstructorService;
use App\Models\Package;

final class InstructorController extends Controller
{
    public function index(Request $request): void
    {
        $packageId = $request->query('package');
        $instructors = Instructor::searchActive($packageId ? ['package_id' => (int) $packageId] : []);

        if ($packageId) {
            foreach ($instructors as &$instructor) {
                $instructor['effective_price'] = InstructorService::effectivePrice($instructor['id'], (int) $packageId);
            }
            unset($instructor);
        }

        $this->success(['instructors' => $instructors]);
    }

    public function show(Request $request, string $id): void
    {
        $instructor = Instructor::findWithName((int) $id);

        if (!$instructor) {
            $this->fail('Instructor not found.', 404);
        }

        $packageId = $request->query('package');

        if ($packageId) {
            $instructor['effective_price'] = InstructorService::effectivePrice($instructor['id'], (int) $packageId);
        }

        $this->success([
            'instructor' => $instructor,
            'reviews' => InstructorReview::forInstructor($instructor['id']),
            'is_favorited' => Favorite::isFavorited(Auth::id(), (int) $instructor['id']),
        ]);
    }

    public function toggleFavorite(Request $request, string $id): void
    {
        $instructorId = (int) $id;

        if (!Instructor::find($instructorId)) {
            $this->fail('Instructor not found.', 404);
        }

        $isFavorited = Favorite::toggle(Auth::id(), $instructorId);

        $this->success(['is_favorited' => $isFavorited], $isFavorited ? 'Added to favorites.' : 'Removed from favorites.');
    }

    public function availability(Request $request, string $id): void
    {
        $instructorId = (int) $id;

        if (!Instructor::find($instructorId)) {
            $this->fail('Instructor not found.', 404);
        }

        $date = $request->query('date');

        if ($date) {
            $packageId = (int) $request->query('package_id');
            $duration = 60;

            if ($packageId) {
                $package = Package::find($packageId);
                $duration = $package['duration_minutes'] ?? 60;
            }

            BookingSlot::ensureGeneratedForDate($instructorId, $date, $duration);

            $this->success([
                'date' => $date,
                'slots' => BookingSlot::findAvailable($instructorId, $date),
            ]);
        }

        $year = (int) $request->query('year', date('Y'));
        $month = (int) $request->query('month', date('n'));

        $this->success($this->monthAvailability($instructorId, $year, $month));
    }

    private function monthAvailability(int $instructorId, int $year, int $month): array
    {
        $windows = InstructorAvailability::forInstructor($instructorId);
        $blocked = array_column(InstructorBlockedDate::forInstructor($instructorId), 'blocked_date');

        $daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
        $today = date('Y-m-d');

        $available = [];
        $unavailable = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);

            if ($dateStr < $today) {
                continue;
            }

            if (in_array($dateStr, $blocked, true)) {
                $unavailable[] = $dateStr;
                continue;
            }

            $dayOfWeek = (int) date('w', strtotime($dateStr));
            $hasWindow = false;

            foreach ($windows as $window) {
                $matches = (int) $window['is_recurring'] === 1
                    ? (int) $window['day_of_week'] === $dayOfWeek
                    : $window['specific_date'] === $dateStr;

                if ($matches) {
                    $hasWindow = true;
                    break;
                }
            }

            if ($hasWindow) {
                $available[] = $dateStr;
            } else {
                $unavailable[] = $dateStr;
            }
        }

        return ['year' => $year, 'month' => $month, 'available' => $available, 'unavailable' => $unavailable];
    }
}
