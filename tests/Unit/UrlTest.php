<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Url;
use Carbon\Carbon;

class UrlTest extends TestCase
{
    // Проверка нормализации URL (удаляются путь и параметры)
    public function testNormalizeNameWithValidUrl()
    {
        $url = new Url('https://example.com/some/path?query=string');
        $this->assertEquals('https://example.com', $url->getName());
    }

    // Проверка выбрасывания исключения для некорректного URL
    public function testNormalizeNameWithInvalidUrlThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Url('invalid-url');
    }

    // Проверка установки id и created_at в конструкторе
    public function testConstructorSetsIdAndCreatedAt()
    {
        $createdAt = new Carbon('2024-01-01 12:00:00');
        $url = new Url('https://example.com', 123, $createdAt);

        $this->assertEquals(123, $url->getId());
        $this->assertEquals($createdAt->toDateTimeString(), $url->getCreatedAt()->toDateTimeString());
    }
}
