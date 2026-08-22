<?php

namespace Batch\Business\Dtos;

use Shared\Contracts;

class CreateDto
{
    public string $name;
    public string $description;

    public function __construct(string $name, string $description)
    {
        Contracts::requiresNotNullOrEmpty($name, 'Name');
        Contracts::requiresNotNullOrEmpty($description, 'Description');

        $this->name = $name;
        $this->description = $description;
    }
}
