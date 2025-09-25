<?php

namespace App;

use Carbon\Carbon;

class UrlsRepository
{
    private \PDO $pdo;

    public function __construct(\PDO $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Сохраняет новый URL в базу данных.
     * Возвращает объект Url с обновлённым ID и временем создания.
     */
    public function save(Url $url): Url
    {
        $sql = 'INSERT INTO urls (name, created_at) VALUES (:name, :created_at)';
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'name' => $url->getName(),
            'created_at' => $url->getCreatedAt()->toDateTimeString(), // Carbon → строка
        ]);

        // Получаем ID вставленной записи и возвращаем обновлённый объект
        return new Url(
            $url->getName(),
            (int) $this->pdo->lastInsertId(),
            $url->getCreatedAt()
        );
    }

    /**
     * Находит URL по ID или возвращает null, если не найден.
     */
    public function find(int $id): ?Url
    {
        $stmt = $this->pdo->prepare('SELECT * FROM urls WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return new Url(
            $data['name'],
            (int) $data['id'],
            new Carbon($data['created_at'])
        );
    }

    /**
     * Ищет URL по имени (например, чтобы проверить дубликат).
     */
    public function findByName(string $name): ?Url
    {
        $stmt = $this->pdo->prepare('SELECT * FROM urls WHERE name = :name');
        $stmt->execute(['name' => $name]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return new Url(
            $data['name'],
            (int) $data['id'],
            new Carbon($data['created_at'])
        );
    }

    /**
     * Возвращает все URL в виде массива объектов Url.
     */
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM urls ORDER BY id DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Url(
            $row['name'],
            (int) $row['id'],
            new \Carbon\Carbon($row['created_at'])
        ), $rows);
    }
}
