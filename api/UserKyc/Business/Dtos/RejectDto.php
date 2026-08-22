<?php

namespace UserKyc\Business\Dtos;

use Shared\Contracts;

class RejectDto
{
    public int $id;
    public int $rejected_by;
    public string $reject_reason;

    public function __construct(int $id, int $rejected_by, string $reject_reason)
    {
        Contracts::requires($id > 0, 'KYC ID is required');
        Contracts::requires($rejected_by > 0, 'Rejected by is required');
        Contracts::requiresNotNullOrEmpty($reject_reason, 'Reject Reason');

        $this->id = $id;
        $this->rejected_by = $rejected_by;
        $this->reject_reason = $reject_reason;
    }
}
