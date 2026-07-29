<?php 

namespace Presentation\Http\Middlewares;

use Presentation\ApiCredential\ApiCredentialServiceInterface;
use R2Packages\Framework\Infrastructure\Framework\Container\Request;
use R2Packages\Framework\Infrastructure\Framework\Middlewares\MiddlewareServiceInterface;


class GlobalApiMiddleware implements MiddlewareServiceInterface
{
    private ApiCredentialServiceInterface $apiCredentialService;
    private Request $request;
    
    public function __construct(ApiCredentialServiceInterface $apiCredentialService, Request $request){
        $this->request = $request;
        $this->apiCredentialService = $apiCredentialService;
    }

    public function handle(){
        $xToken = $this->request->get('x-token');
        $this->apiCredentialService->validateToken($xToken);
    }
}