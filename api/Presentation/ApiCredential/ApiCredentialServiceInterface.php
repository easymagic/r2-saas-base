<?php 

namespace Presentation\ApiCredential;

use Domain\User\UserEntity;

interface ApiCredentialServiceInterface
{
    /**
     * Get the authenticated user
     * @return UserEntity
     */
    public function getAuthUser();
    public function validateToken(string $x_token);
    public function validateUserToken(string $x_user_token);
}