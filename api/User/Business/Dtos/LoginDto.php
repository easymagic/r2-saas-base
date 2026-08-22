<?php 
namespace User\Business\Dtos;

use Shared\Contracts;

class LoginDto
{
    public string $email;
    public string $password;


    public function __construct(string $email, string $password)
    {
        
        Contracts::requiresNotNullOrEmpty($email, 'Email');
        Contracts::requiresNotNullOrEmpty($password, 'Password');

        $this->email = $email;
        $this->password = $password;
    }
}