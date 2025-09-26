<?php

namespace App;

use Carbon\Carbon;

class Url
{
    private ?int $id = null;
    private string $name;
    private Carbon $createdAt;

    public function __construct(string $name, ?int $id = null, ?Carbon $createdAt = null)
    {
        $this->name = $this->normalizeName($name);
        $this->id = $id;
        $this->createdAt = $createdAt ?? Carbon::now();
    }

    private function normalizeName(string $url): string
    {
        $parts = parse_url($url);

        if (!isset($parts['scheme'], $parts['host'])) {
            throw new \InvalidArgumentException('Невозможно нормализовать URL');
        }

        return $parts['scheme'] . '://' . $parts['host'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }
}
