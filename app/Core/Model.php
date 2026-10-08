<?php
namespace App\Core;

abstract class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $allowedFields = [];

    public function getTable(): string
    {
        return $this->table;
    }

    public function find(int|string $id): ?array
    {
        return Database::table($this->table)->where($this->primaryKey, $id)->first();
    }

    public function findAll(int $limit = 500, int $offset = 0): array
    {
        return Database::table($this->table)->limit($limit, $offset)->get();
    }

    public function where(string $column, mixed $value): QueryBuilder
    {
        return Database::table($this->table)->where($column, $value);
    }

    public function insert(array $data): int
    {
        $filtered = array_intersect_key($data, array_flip($this->allowedFields));
        if (empty($filtered)) {
            $filtered = $data;
        }
        return Database::table($this->table)->insert($filtered);
    }

    public function update(int|string $id, array $data): bool
    {
        $filtered = array_intersect_key($data, array_flip($this->allowedFields));
        if (empty($filtered)) {
            $filtered = $data;
        }
        return Database::table($this->table)->where($this->primaryKey, $id)->update($filtered);
    }

    public function delete(int|string $id): bool
    {
        return Database::table($this->table)->where($this->primaryKey, $id)->delete();
    }

    public function count(): int
    {
        return Database::table($this->table)->count();
    }
}
