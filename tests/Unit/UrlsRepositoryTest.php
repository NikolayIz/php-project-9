<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Url;
use App\UrlsRepository;
use PDO;

class UrlsRepositoryTest extends TestCase
{
    private PDO $pdo;
    private UrlsRepository $repository;

    protected function setUp(): void
    {
        // Подключение к in-memory SQLite
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Создаем таблицу urls
        $this->pdo->exec('
            CREATE TABLE urls (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                created_at TEXT
            )
        ');

        $this->repository = new UrlsRepository($this->pdo);
    }

    // Вспомогательный метод для создания и сохранения URL
    private function createUrl(string $name = 'https://example.com'): Url
    {
        $url = new Url($name);
        return $this->repository->save($url);
    }

    // Сохраняем URL и проверяем, что вернулся объект с ID
    public function testSaveReturnsUrlWithId(): void
    {
        $savedUrl = $this->createUrl();

        $this->assertNotNull($savedUrl->getId());
        $this->assertSame('https://example.com', $savedUrl->getName());
    }

    // Находим существующий URL по ID
    public function testFindExistingUrl(): void
    {
        $savedUrl = $this->createUrl();
        $foundUrl = $this->repository->find($savedUrl->getId());

        $this->assertNotNull($foundUrl);
        $this->assertSame($savedUrl->getId(), $foundUrl->getId());
        $this->assertSame($savedUrl->getName(), $foundUrl->getName());
    }

    // Проверяем, что поиск несуществующего URL возвращает null
    public function testFindNonExistingUrlReturnsNull(): void
    {
        $this->assertNull($this->repository->find(999));
    }

    // Поиск URL по имени
    public function testFindByName(): void
    {
        $this->createUrl();

        $foundUrl = $this->repository->findByName('https://example.com');
        $this->assertNotNull($foundUrl);
        $this->assertSame('https://example.com', $foundUrl->getName());

        $this->assertNull($this->repository->findByName('https://nonexistent.com'));
    }

    // Проверка, что метод all() возвращает все URL в правильном порядке
    public function testAllReturnsArrayOfUrls(): void
    {
        $this->createUrl('https://example1.com');
        $this->createUrl('https://example2.com');

        $allUrls = $this->repository->all();
        $this->assertIsArray($allUrls);
        $this->assertCount(2, $allUrls);
        $this->assertSame('https://example1.com', $allUrls[0]->getName());
        $this->assertSame('https://example2.com', $allUrls[1]->getName());
    }
}
