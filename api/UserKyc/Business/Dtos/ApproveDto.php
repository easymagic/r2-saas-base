<?php

namespace UserKyc\Business\Dtos;

use Shared\Contracts;

class ApproveDto
{
    public int $id;
    public int $approved_by;

    public function __construct(int $id, int $approved_by)
    {
        Contracts::requires($id > 0, 'KYC ID is required');
        Contracts::requires($approved_by > 0, 'Approved by is required');

        $this->id = $id;
        $this->approved_by = $approved_by;
    }
}
