<?php

namespace App\Models;

use App\Core\Model;

final class Instructor extends Model
{
    protected static string $table = 'instructors';

    public static function findByUserId(int $userId): ?array
    {
        return self::whereFirst(['user_id' => $userId]);
    }

    public static function searchActive(array $filters = []): array
    {
        $params = ['status' => 'active'];
        $sql = 'SELECT instructors.*, users.name FROM instructors INNER JOIN users ON users.id = instructors.user_id';

        if (!empty($filters['package_id'])) {
            $sql .= ' INNER JOIN instructor_services ON instructor_services.instructor_id = instructors.id'
                . ' AND instructor_services.package_id = :package_id AND instructor_services.is_active = 1';
            $params['package_id'] = $filters['package_id'];
        }

        $sql .= ' WHERE instructors.status = :status';

        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);

        return array_map(static fn (array $row) => self::hydrate($row), $stmt->fetchAll());
    }

    public static function adminList(): array
    {
        $stmt = static::db()->query(
            'SELECT instructors.*, users.name, users.email, users.phone
             FROM instructors
             INNER JOIN users ON users.id = instructors.user_id
             ORDER BY instructors.status ASC, users.name ASC'
        );

        return array_map(static fn (array $row): array => self::hydrate($row), $stmt->fetchAll());
    }

    public static function findWithName(int $id): ?array
    {
        $stmt = static::db()->prepare(
            'SELECT instructors.*, users.name FROM instructors
             INNER JOIN users ON users.id = instructors.user_id
             WHERE instructors.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? self::hydrate($row) : null;
    }

    public static function hydrate(array $row): array
    {
        $row['certificates'] = $row['certificates'] !== null ? json_decode($row['certificates'], true) : null;
        $row['specialties'] = $row['specialties'] !== null ? json_decode($row['specialties'], true) : null;

        return $row;
    }
}
