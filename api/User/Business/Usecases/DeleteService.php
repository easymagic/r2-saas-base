<?php
namespace User\Business\Usecases;

use Exception;
use Shared\Contracts;

class DeleteService
{
    public function execute(int $id)
    {
        Contracts::requires($id > 0, 'ID is required');
        throw new Exception('Delete not allowed');
    }
}
