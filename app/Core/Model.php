<?php

namespace App\Core;

use PDO;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    protected static function db(): PDO
    {
        return Database::connection();
    }

    public static function find(int|string $id): ?array
    {
        $stmt = static::db()->prepare(sprintf(
            'SELECT * FROM %s WHERE %s = :id LIMIT 1',
            static::$table,
            static::$primaryKey
        ));
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function findBy(string $column, mixed $value): ?array
    {
        $stmt = static::db()->prepare(sprintf(
            'SELECT * FROM %s WHERE %s = :value LIMIT 1',
            static::$table,
            $column
        ));
        $stmt->execute(['value' => $value]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public static function all(string $orderBy = ''): array
    {
        $sql = sprintf('SELECT * FROM %s', static::$table);
        if ($orderBy !== '') {
            $sql .= ' ORDER BY ' . $orderBy;
        }

        return static::db()->query($sql)->fetchAll();
    }

    public static function where(array $conditions, string $orderBy = '', int $limit = 0): array
    {
        [$clause, $params] = self::buildWhere($conditions);
        $sql = sprintf('SELECT * FROM %s%s', static::$table, $clause);

        if ($orderBy !== '') {
            $sql .= ' ORDER BY ' . $orderBy;
        }
        if ($limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }

        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function whereFirst(array $conditions): ?array
    {
        $rows = static::where($conditions, '', 1);

        return $rows[0] ?? null;
    }

    public static function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(static fn ($c) => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = static::db()->prepare($sql);
        $stmt->execute($data);

        return (int) static::db()->lastInsertId();
    }

    public static function update(int|string $id, array $data): bool
    {
        $assignments = implode(', ', array_map(static fn ($c) => "{$c} = :{$c}", array_keys($data)));
        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :__id',
            static::$table,
            $assignments,
            static::$primaryKey
        );

        $data['__id'] = $id;
        $stmt = static::db()->prepare($sql);

        return $stmt->execute($data);
    }

    public static function delete(int|string $id): bool
    {
        $stmt = static::db()->prepare(sprintf(
            'DELETE FROM %s WHERE %s = :id',
            static::$table,
            static::$primaryKey
        ));

        return $stmt->execute(['id' => $id]);
    }

    public static function count(array $conditions = []): int
    {
        [$clause, $params] = self::buildWhere($conditions);
        $stmt = static::db()->prepare(sprintf('SELECT COUNT(*) AS c FROM %s%s', static::$table, $clause));
        $stmt->execute($params);

        return (int) $stmt->fetch()['c'];
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private static function buildWhere(array $conditions): array
    {
        if (empty($conditions)) {
            return ['', []];
        }

        $parts = [];
        $params = [];
        foreach ($conditions as $column => $value) {
            $paramKey = str_replace('.', '_', $column);
            $parts[] = "{$column} = :{$paramKey}";
            $params[$paramKey] = $value;
        }

        return [' WHERE ' . implode(' AND ', $parts), $params];
    }
}
