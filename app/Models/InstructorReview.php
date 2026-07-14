<?php

namespace App\Models;

use App\Core\Model;

final class InstructorReview extends Model
{
    protected static string $table = 'instructor_reviews';

    public static function forInstructor(int $instructorId): array
    {
        return self::where(['instructor_id' => $instructorId], 'created_at DESC');
    }

    public static function existsForBooking(int $bookingId): bool
    {
        return self::whereFirst(['booking_id' => $bookingId]) !== null;
    }

    public static function create(array $data): int
    {
        $id = self::insert($data);

        $stmt = static::db()->prepare(
            'SELECT AVG(rating) AS avg_rating, COUNT(*) AS review_count FROM instructor_reviews WHERE instructor_id = :id'
        );
        $stmt->execute(['id' => $data['instructor_id']]);
        $stats = $stmt->fetch();

        Instructor::update($data['instructor_id'], [
            'rating_avg' => round((float) $stats['avg_rating'], 2),
            'rating_count' => (int) $stats['review_count'],
        ]);

        return $id;
    }
}
