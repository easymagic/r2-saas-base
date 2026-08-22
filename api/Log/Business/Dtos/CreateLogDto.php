<?php

namespace Log\Business\Dtos;

use Shared\Contracts;

class CreateLogDto
{
    public string $title;
    public string $payload;
    public string $response;
    public string $type;

    public function __construct(string $title, string $payload, string $response, string $type)
    {
        Contracts::requiresNotNullOrEmpty($title, 'Title');
        $type = strtolower(trim($type));
        Contracts::requiresInArray($type, ['success', 'error', 'info'], 'Type');

        $this->title = $title;
        $this->payload = $payload;
        $this->response = $response;
        $this->type = $type;
    }
}
