<?php

namespace App;

use Carbon\Carbon;

class Check
{
    private ?int $id;
    private int $urlId;
    private ?int $statusCode;
    private ?string $h1;
    private ?string $title;
    private ?string $description;
    private Carbon $createdAt;

    public function __construct(
        int $urlId,
        ?int $id = null,
        ?int $statusCode = null,
        ?string $h1 = null,
        ?string $title = null,
        ?string $description = null,
        ?Carbon $createdAt = null
    ) {
        $this->id = $id;
        $this->urlId = $urlId;
        $this->statusCode = $statusCode;
        $this->h1 = $h1;
        $this->title = $title;
        $this->description = $description;
        $this->createdAt = $createdAt ?? Carbon::now();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrlId(): int
    {
        return $this->urlId;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getH1(): ?string
    {
        return $this->h1;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt->toDateTimeString();
    }
}
