<?php 

namespace Presentation\ApiCredential;

use Domain\User\UserRepositoryInterface;
use Domain\User\UserEntity;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Env\EnvServiceInterface;

class ApiCredentialService implements ApiCredentialServiceInterface
{
    private Request $request;
    private static UserEntity $user;
    private UserRepositoryInterface $userRepository;
    private EnvServiceInterface $envService;

    public function __construct(Request $request, UserRepositoryInterface $userRepository, EnvServiceInterface $envService){
        $this->request = $request;
        $this->userRepository = $userRepository;
        $this->envService = $envService;
    }

    public function getAuthUser(){
        return self::$user;
    }

    public function validateToken(string $x_token){
        if($x_token !== $this->envService->get('X_TOKEN')){
            throw new \Exception('Invalid token');
        }

        return true;
    }

    public function validateUserToken(string $x_user_token){

        if (empty($x_user_token)){
            throw new \Exception('User token is required');
        }

        $split = explode('_', $x_user_token);
        if (count($split) !== 2){
            throw new \Exception('Invalid user token');
        }
        $user_id = $split[0];
        $user_token = $x_user_token;
        $user = $this->userRepository->find($user_id);

        if ($user->token !== $user_token){
            throw new \Exception('Invalid user token');
        }

        self::$user = $user;
        return true;
    }

}