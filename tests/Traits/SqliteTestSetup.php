<?php

namespace Tests\Traits;

use PDO;

trait SqliteTestSetup
{
    protected ?PDO $pdo = null;

    // Создаёт подключение к in-memory SQLite и запускает SQL из файла.
    protected function setUpDatabase(string $sqlFile): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = file_get_contents($sqlFile);

        // SQLite не понимает SERIAL, BIGINT, ON DELETE CASCADE и т.д. в Postgres, поэтому:
        $sql = str_replace(
            ['SERIAL', 'bigint', 'ON DELETE CASCADE', 'VARCHAR'],
            ['INTEGER', 'INTEGER', '', 'TEXT'],
            $sql
        );

        $this->pdo->exec($sql);
    }

    // Закрываем подключение
    protected function tearDownDatabase(): void
    {
        $this->pdo = null;
    }
}
