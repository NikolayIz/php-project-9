<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Check;
use App\ChecksRepository;
use PDO;
use Carbon\Carbon;

class ChecksRepositoryTest extends TestCase
{
    private PDO $pdo;
    private ChecksRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Создаем таблицы для ссылок и проверок
        $this->pdo->exec('
            CREATE TABLE urls (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                created_at TEXT
            );
        ');

        $this->pdo->exec('
            CREATE TABLE url_checks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                url_id INTEGER,
                status_code INTEGER,
                h1 TEXT,
                title TEXT,
                description TEXT,
                created_at TEXT
            );
        ');

        $this->repository = new ChecksRepository($this->pdo);
    }

    // Вспомогательный метод для создания проверки
    private function createCheck(
        int $urlId = 1,
        int $statusCode = 200,
        ?string $h1 = null,
        ?string $title = null,
        ?string $description = null,
        ?Carbon $createdAt = null
    ): Check {
        return new Check(
            urlId: $urlId,
            statusCode: $statusCode,
            h1: $h1,
            title: $title,
            description: $description,
            createdAt: $createdAt ?? Carbon::now()
        );
    }

    // Сохраняем проверку и проверяем, что вернулся объект с ID
    public function testSaveReturnsCheckWithId(): void
    {
        $check = $this->createCheck();
        $savedCheck = $this->repository->save($check);

        $this->assertNotNull($savedCheck->getId());
        $this->assertSame(1, $savedCheck->getUrlId());
        $this->assertSame(200, $savedCheck->getStatusCode());
    }

    // Проверка метода all()
    public function testAllReturnsArrayOfChecks(): void
    {
        $this->repository->save($this->createCheck(urlId: 1));
        $this->repository->save($this->createCheck(urlId: 2));

        $allChecks = $this->repository->all();
        $this->assertIsArray($allChecks);
        $this->assertCount(2, $allChecks);
        $this->assertSame(1, $allChecks[0]->getUrlId());
        $this->assertSame(2, $allChecks[1]->getUrlId());
    }

    // Проверка метода findAllByUrlId()
    public function testFindAllByUrlIdReturnsChecksForUrl(): void
    {
        $this->repository->save($this->createCheck(urlId: 1));
        $this->repository->save($this->createCheck(urlId: 1));
        $this->repository->save($this->createCheck(urlId: 2));

        $checks = $this->repository->findAllByUrlId(1);
        $this->assertCount(2, $checks);
        $this->assertSame(1, $checks[0]->getUrlId());
        $this->assertSame(1, $checks[1]->getUrlId());
    }

    // Проверка метода findLastCheckByUrlId()
    public function testFindLastCheckByUrlIdReturnsDate(): void
    {
        $check1 = $this->createCheck(urlId: 1, createdAt: Carbon::now());
        $check2 = $this->createCheck(urlId: 1, createdAt: Carbon::now()->addSecond());
        $this->repository->save($check1);
        $this->repository->save($check2);

        $lastDate = $this->repository->findLastCheckByUrlId(1);
        $this->assertEquals($check2->getCreatedAt(), $lastDate);
    }

    // Проверка метода findLastStatusCodeByUrlId()
    public function testFindLastStatusCodeByUrlIdReturnsCode(): void
    {
        $this->repository->save($this->createCheck(urlId: 1, statusCode: 200, createdAt: Carbon::now()));
        $this->repository->save($this->createCheck(
            urlId: 1,
            statusCode: 404,
            createdAt:
            Carbon::now()->addSecond()
        ));

        $lastCode = $this->repository->findLastStatusCodeByUrlId(1);
        $this->assertSame(404, $lastCode);
    }
}
