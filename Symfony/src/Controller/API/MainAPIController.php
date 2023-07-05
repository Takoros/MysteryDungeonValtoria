<?php

namespace App\Controller\API;

use App\Service\APIService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainAPIController extends AbstractAPIController
{   
    private $apiService;

    public function __construct(APIService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Verify Token and reply 'pong' if good
     */
    #[Route('/api/ping', name: 'api_ping', methods:["POST"])]
    public function ping(Request $request): JsonResponse
    {
        $post = json_decode($request->getContent());
        $isValid = $this->verifyTokenAndData($post, [], $this->apiService);

        return new JsonResponse(['message' => 'pong !'], 200);
    }

    #[Route('/get/token', name: 'api_ping', methods:["POST"])]
    public function getToken(Request $request): JsonResponse
    {
        $post = json_decode($request->getContent());

        if($post->mdp !== '2203'){
            return new JsonResponse(['toto'=> 'toto']);
        }

        return new JsonResponse(['token' => $this->apiService->getApiToken()]);
    }
}
