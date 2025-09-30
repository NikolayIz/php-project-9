<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\UrlValidator;

class UrlValidatorTest extends TestCase
{
    // Проверка корректного URL — ошибок быть не должно
    public function testValidUrlReturnsNoErrors()
    {
        $data = ['name' => 'https://example.com'];
        $errors = UrlValidator::validate($data);
        $this->assertEmpty($errors);
    }

    // Проверка пустого URL — должна быть ошибка
    public function testEmptyUrlReturnsError()
    {
        $data = ['name' => ''];
        $errors = UrlValidator::validate($data);
        $this->assertNotEmpty($errors);
        $this->assertContains('URL не должен быть пустым', $errors);
    }

    // Проверка некорректного URL
    public function testInvalidUrlReturnsError()
    {
        $data = ['name' => 'invalid-url'];
        $errors = UrlValidator::validate($data);
        $this->assertNotEmpty($errors);
        $this->assertContains('Некорректный URL', $errors);
    }

    // Проверка слишком длинного URL
    public function testUrlExceedsMaxLengthReturnsError()
    {
        $longUrl = 'http://' . str_repeat('a', 250) . '.com';
        $data = ['name' => $longUrl];
        $errors = UrlValidator::validate($data);
        $this->assertNotEmpty($errors);
        $this->assertContains('URL не должен превышать 255 символов', $errors);
    }
}
