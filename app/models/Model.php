<?php
/**
 * AsaanFix Pakistan - Base Model
 * Provides common database operations for all models
 * 
 * MVC Pattern: Models handle all database interactions
 */

class Model {
    protected string $table = '';
    protected string $primaryKey = 'id';

    /**
     * Find a record by ID
     */
    public function find(int $id): ?array {
        return dbQueryOne("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]);
    }

    /**
     * Find by a specific column
     */
    public function findBy(string $column, mixed $value): ?array {
        return dbQueryOne("SELECT * FROM {$this->table} WHERE $column = ?", [$value]);
    }

    /**
     * Get all records with optional conditions
     */
    public function all(string $orderBy = 'id ASC', int $limit = 100): array {
        return dbQuery("SELECT * FROM {$this->table} ORDER BY $orderBy LIMIT ?", [$limit]);
    }

    /**
     * Get filtered records
     */
    public function where(string $conditions, array $params = [], string $orderBy = 'id ASC'): array {
        return dbQuery("SELECT * FROM {$this->table} WHERE $conditions ORDER BY $orderBy", $params);
    }

    /**
     * Count records
     */
    public function count(string $conditions = '1=1', array $params = []): int {
        $result = dbQueryOne("SELECT COUNT(*) as cnt FROM {$this->table} WHERE $conditions", $params);
        return $result['cnt'] ?? 0;
    }

    /**
     * Insert a new record
     */
    public function create(array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        dbExecute("INSERT INTO {$this->table} ($columns) VALUES ($placeholders)", array_values($data));
        return dbLastId();
    }

    /**
     * Update a record by ID
     */
    public function update(int $id, array $data): int {
        $setClause = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
        $params = array_values($data);
        $params[] = $id;
        return dbExecute("UPDATE {$this->table} SET $setClause WHERE {$this->primaryKey} = ?", $params);
    }

    /**
     * Delete a record by ID
     */
    public function delete(int $id): int {
        return dbExecute("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?", [$id]);
    }

    /**
     * Paginate results
     */
    public function paginate(int $page = 1, int $perPage = 12, string $conditions = '1=1', array $params = []): array {
        $total = $this->count($conditions, $params);
        $pagination = getPagination($total, $page, $perPage);
        $results = dbQuery(
            "SELECT * FROM {$this->table} WHERE $conditions ORDER BY {$this->primaryKey} DESC LIMIT ? OFFSET ?",
            array_merge($params, [$pagination['per_page'], $pagination['offset']])
        );
        return ['data' => $results, 'pagination' => $pagination];
    }
}
