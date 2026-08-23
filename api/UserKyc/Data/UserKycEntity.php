<?php

namespace UserKyc\Data;

use Shared\AbstractBaseEntity;

class UserKycEntity extends AbstractBaseEntity
{
    public int $user_id = 0;
    public string $nin = '';
    public string $store_name = '';
    public string $description = '';
    public string $document1 = '';
    public string $document2 = '';
    public string $document3 = '';
    public string $document4 = '';
    public string $document5 = '';
    public int $approved = -1;
    public int $approved_by = 0;
    public string $reject_reason = '';
    public string $created_at = '';
    public string $updated_at = '';

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->correctDateDefaults(empty($this->created_at) || empty($this->updated_at));
    }

    private function correctDateDefaults(bool $condition){
        if ($condition) {
            $this->created_at = date('Y-m-d H:i:s');
            $this->updated_at = date('Y-m-d H:i:s');
        }
    }
}
