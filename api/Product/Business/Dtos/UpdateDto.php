<?php

namespace Product\Business\Dtos;

use Shared\Contracts;

class UpdateDto
{
    public int $id;
    public string $name;
    public string $description;
    public float $price;
    public float $old_price;
    public int $stock_qty;
    public int $category_id;
    public int $user_id;
    public string $slug;
    public int $active;
    public array $image_1;
    public array $image_2;
    public array $image_3;
    public array $image_4;
    public array $image_5;
    public array $image_6;
    public array $image_7;

    public function __construct(
        int $id,
        string $name,
        string $description,
        float $price,
        float $old_price,
        int $stock_qty,
        int $category_id,
        int $user_id,
        string $slug,
        int $active,
        array $image_1 = [],
        array $image_2 = [],
        array $image_3 = [],
        array $image_4 = [],
        array $image_5 = [],
        array $image_6 = [],
        array $image_7 = []
    ) {
        Contracts::requires($id > 0, 'Product ID is required');
        Contracts::requiresNotNullOrEmpty(trim($name), 'Name');
        Contracts::requires($user_id > 0, 'User ID is required');
        Contracts::requires($category_id > 0, 'Category ID is required');

        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->old_price = $old_price;
        $this->stock_qty = $stock_qty;
        $this->category_id = $category_id;
        $this->user_id = $user_id;
        $this->slug = $slug;
        $this->active = $active;
        $this->image_1 = $image_1;
        $this->image_2 = $image_2;
        $this->image_3 = $image_3;
        $this->image_4 = $image_4;
        $this->image_5 = $image_5;
        $this->image_6 = $image_6;
        $this->image_7 = $image_7;
    }
}
