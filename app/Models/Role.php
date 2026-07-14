<?php

namespace App\Models;

use App\Core\Model;

final class Role extends Model
{
    protected static string $table = 'roles';

    public static function findBySlug(string $slug): ?array
    {
        return self::whereFirst(['slug' => $slug]);
    }

    /**
     * @return string[] permission slugs granted to this role
     */
    public static function permissionSlugsForRole(string $roleSlug): array
    {
        $stmt = static::db()->prepare(
            'SELECT p.slug FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             INNER JOIN roles r ON r.id = rp.role_id
             WHERE r.slug = :slug'
        );
        $stmt->execute(['slug' => $roleSlug]);

        return array_column($stmt->fetchAll(), 'slug');
    }
}
