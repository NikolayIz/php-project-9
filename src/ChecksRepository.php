<?php

namespace App;

use Carbon\Carbon;

class ChecksRepository
{
    private \PDO $pdo;

    public function __construct(\PDO $conn)
    {
        $this->pdo = $conn;
    }

    public function save(Check $check): Check
    {
        $sql = 'INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at)
            VALUES (:url_id, :status_code, :h1, :title, :description, :created_at)';
        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'url_id' => $check->getUrlId(),
            'status_code' => $check->getStatusCode(),
            'h1' => $check->getH1(),
            'title' => $check->getTitle(),
            'description' => $check->getDescription(),
            'created_at' => new \Carbon\Carbon($check->getCreatedAt()), // строка -> Carbon
        ]);

        // Получаем ID вставленной записи и возвращаем обновлённый объект
        return new Check(
            url_id: $check->getUrlId(),
            id: (int) $this->pdo->lastInsertId(),
            status_code: $check->getStatusCode(),
            created_at: new \Carbon\Carbon($check->getCreatedAt())
        );
    }
    /**
     * Возвращает все СHecks в виде массива объектов Check
     */
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM url_checks ORDER BY created_at DESC');
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($row) => new Check(
            url_id: (int) $row['url_id'],
            id: (int) $row['id'],
            status_code: $row['status_code'] !== null ? (int) $row['status_code'] : null,
            h1: $row['h1'],
            title: $row['title'],
            description: $row['description'],
            created_at: new \Carbon\Carbon($row['created_at'])
        ), $rows);
    }

    public function findAllByUrlId(int $urlId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM url_checks WHERE url_id = :url_id ORDER BY created_at DESC');
        $stmt->execute(['url_id' => $urlId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => new Check(
            url_id: (int) $row['url_id'],
            id: (int) $row['id'],
            status_code: (int) $row['status_code'] ?? null,
            h1: $row['h1'],
            title: $row['title'],
            description: $row['description'],
            created_at: new \Carbon\Carbon($row['created_at'])
        ), $rows);
    }

    public function findLastCheckByUrlId(int $urlId): ?string
    {
        $stmt = $this->pdo->prepare('SELECT * FROM url_checks WHERE url_id = :url_id ORDER BY created_at DESC LIMIT 1');
        $stmt->execute(['url_id' => $urlId]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $data['created_at'];
    }

    public function findLastStatusCodeByUrlId(int $urlId): ?int
    {
        $stmt = $this->pdo->prepare('SELECT * FROM url_checks WHERE url_id = :url_id ORDER BY created_at DESC LIMIT 1');
        $stmt->execute(['url_id' => $urlId]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return (int) $data['status_code'];
    }
}
