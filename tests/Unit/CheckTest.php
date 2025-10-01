<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Check;
use Carbon\Carbon;

class CheckTest extends TestCase
{
    // устанавливаем все значения и проверяем геттеры
    public function testConstructorSetsAllFields(): void
    {
        $createdAt = new \Carbon\Carbon('2024-01-01 12:00:00');
        $check = new \App\Check(
            urlId: 1,
            id: 123,
            statusCode: 200,
            h1: 'Test H1',
            title: 'Test Title',
            description: 'Test Description',
            createdAt: $createdAt
        );
        $this->assertEquals(123, $check->getId());
        $this->assertEquals(1, $check->getUrlId());
        $this->assertEquals(200, $check->getStatusCode());
        $this->assertEquals('Test H1', $check->getH1());
        $this->assertEquals('Test Title', $check->getTitle());
        $this->assertEquals('Test Description', $check->getDescription());
        $this->assertEquals($createdAt, $check->getCreatedAt());
    }

    // если created_at не передан, ставится текущее время
    public function testCreatedAtDefaultsToNow(): void
    {
        $now = Carbon::now();
        // фиксанция текущего времени для тестов
        Carbon::setTestNow($now);

        $check = new Check(urlId: 1);

        $this->assertEquals($now, $check->getCreatedAt());
        // сброс фиксации
        Carbon::setTestNow();
    }
}
