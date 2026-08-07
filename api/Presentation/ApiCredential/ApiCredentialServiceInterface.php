<?php 

namespace Presentation\ApiCredential;

use User\Data\UserEntity;

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