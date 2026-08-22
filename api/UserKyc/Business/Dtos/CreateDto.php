<?php

namespace UserKyc\Business\Dtos;

use Shared\Contracts;

class CreateDto
{
    public int $user_id;
    public string $nin;
    public string $store_name;
    public string $description;
    public array $document1;
    public array $document2;
    public array $document3;
    public array $document4;
    public array $document5;

    public function __construct(
        int $user_id,
        string $nin,
        string $store_name,
        string $description,
        array $document1 = [],
        array $document2 = [],
        array $document3 = [],
        array $document4 = [],
        array $document5 = []
    ) {
        Contracts::requires($user_id > 0, 'User ID is required');
        Contracts::requiresNotNullOrEmpty($nin, 'NIN');
        Contracts::requiresNotNullOrEmpty($store_name, 'Store Name');
        Contracts::requiresNotNullOrEmpty($description, 'Description');

        $this->user_id = $user_id;
        $this->nin = $nin;
        $this->store_name = $store_name;
        $this->description = $description;
        $this->document1 = $document1;
        $this->document2 = $document2;
        $this->document3 = $document3;
        $this->document4 = $document4;
        $this->document5 = $document5;
    }
}
