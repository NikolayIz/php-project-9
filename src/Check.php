<?php

namespace App;

use Carbon\Carbon;

class Check
{
    private ?int $id = null;
    private int $url_id;
    private ?int $status_code = null;
    private ?string $h1 = null;
    private ?string $title = null;
    private ?string $description = null;
    private Carbon $created_at;

    public function __construct(
        int $url_id,
        ?int $id = null,
        ?int $status_code = null,
        ?string $h1 = null,
        ?string $title = null,
        ?string $description = null,
        ?Carbon $created_at = null
    ) {
        $this->id = $id;
        $this->url_id = $url_id;
        $this->status_code = $status_code;
        $this->h1 = $h1;
        $this->title = $title;
        $this->description = $description;
        $this->created_at = $created_at ?? Carbon::now();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrlId(): int
    {
        return $this->url_id;
    }

    public function getStatusCode(): ?int
    {
        return $this->status_code;
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
        return $this->created_at->toDateTimeString();
    }
}
