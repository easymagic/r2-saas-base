<?php 

namespace Presentation\ApiCredential;

interface ApiCredentialServiceInterface
{
    public function getAuthUser();
    public function validateToken(string $x_token);
    public function validateUserToken(string $x_user_token);
}