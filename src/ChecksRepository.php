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

    protected function createCheckFromRow(array $row): Check
    {
        return new Check(
            urlId: (int) $row['url_id'],
            id: (int) $row['id'],
            statusCode: $row['status_code'] !== null ? (int) $row['status_code'] : null,
            h1: $row['h1'],
            title: $row['title'],
            description: $row['description'],
            createdAt: new Carbon($row['created_at'])
        );
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
        $row = [
        'url_id' => $check->getUrlId(),
        'id' => (int) $this->pdo->lastInsertId(),
        'status_code' => $check->getStatusCode(),
        'h1' => $check->getH1(),
        'title' => $check->getTitle(),
        'description' => $check->getDescription(),
        'created_at' => $check->getCreatedAt(),
        ];

        return $this->createCheckFromRow($row);
    }
    /**
     * Возвращает все СHecks в виде массива объектов Check
     */
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM url_checks ORDER BY created_at DESC');
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(fn($row) => $this->createCheckFromRow($row), $rows);
    }

    public function findAllByUrlId(int $urlId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM url_checks WHERE url_id = :url_id ORDER BY created_at DESC');
        $stmt->execute(['url_id' => $urlId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->createCheckFromRow($row), $rows);
    }

    public function findLastCreatedAtByUrlId(int $urlId): ?string
    {
        $stmt = $this->pdo->prepare('SELECT * FROM url_checks WHERE url_id = :url_id ORDER BY created_at DESC LIMIT 1');
        $stmt->execute(['url_id' => $urlId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $check = $this->createCheckFromRow($row);
        return $check->getCreatedAt();
    }

    public function findLastStatusCodeByUrlId(int $urlId): ?int
    {
        $stmt = $this->pdo->prepare('SELECT * FROM url_checks WHERE url_id = :url_id ORDER BY created_at DESC LIMIT 1');
        $stmt->execute(['url_id' => $urlId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $check = $this->createCheckFromRow($row);
        return $check->getStatusCode();
    }
}
