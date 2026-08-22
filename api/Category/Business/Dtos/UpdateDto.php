<?php

namespace Category\Business\Dtos;

use Shared\Contracts;

class UpdateDto
{
    public int $id;
    public string $name;
    public int $parent_id;
    public string $description;
    public array $image;
    public string $slug;
    public int $active;

    public function __construct(
        int $id,
        string $name,
        int $parent_id,
        string $description,
        array $image,
        string $slug,
        int $active
    ) {
        Contracts::requires($id > 0, 'Category ID is required');
        $name = trim($name);
        Contracts::requiresNotNullOrEmpty($name, 'Name');

        $this->id = $id;
        $this->name = $name;
        $this->parent_id = $parent_id;
        $this->description = $description;
        $this->image = $image;
        $this->slug = $slug;
        $this->active = $active;
    }
}
