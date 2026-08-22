<?php

namespace Category\Business\Dtos;

use Shared\Contracts;

class CreateDto
{
    public string $name;
    public int $parent_id;
    public string $description;
    public array $image;
    public string $slug;

    public function __construct(string $name, int $parent_id, string $description, array $image, string $slug)
    {
        $name = trim($name);
        Contracts::requiresNotNullOrEmpty($name, 'Name');
        Contracts::requires(strlen($name) <= 100, 'Name should be less than 100 characters');
        Contracts::requiresNotNullOrEmpty(trim($description), 'Description');
        Contracts::requires(strlen($description) <= 1000, 'Description should be less than 1000 characters');

        $this->name = $name;
        $this->parent_id = $parent_id;
        $this->description = $description;
        $this->image = $image;
        $this->slug = $slug;
    }
}
