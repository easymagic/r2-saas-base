<?php

namespace SnappyOrder\Business\Dtos;

use Shared\Contracts;

class CreateDto
{
    public int $user_id;
    public string $link;
    public string $description;
    public array $screen_shot1;
    public array $screen_shot2;
    public array $screen_shot3;
    public float $total_amount_usd;

    public function __construct(
        int $user_id,
        string $link,
        string $description,
        array $screen_shot1,
        array $screen_shot2,
        array $screen_shot3,
        float $total_amount_usd
    ) {
        Contracts::requires($user_id > 0, 'User ID is required');
        Contracts::requiresNotNullOrEmpty($link, 'Link');
        Contracts::requiresNotNullOrEmpty($description, 'Description');
        Contracts::requires($total_amount_usd > 0, 'Total amount is required');
        Contracts::requires(!empty($screen_shot1), 'Screen shot 1 is required');

        $this->user_id = $user_id;
        $this->link = $link;
        $this->description = $description;
        $this->screen_shot1 = $screen_shot1;
        $this->screen_shot2 = $screen_shot2;
        $this->screen_shot3 = $screen_shot3;
        $this->total_amount_usd = $total_amount_usd;
    }
}
