<?php

namespace App\Controller\API;

use App\Service\APIService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainAPIController extends AbstractAPIController
{   
    public array $API_PING_ARGS = [];

    /**
     * Verify Token and reply 'pong' if good
     */
    #[Route('/api/ping', name: 'api_ping', methods:["POST"])]
    public function ping(): JsonResponse
    {
        return new JsonResponse(['message' => 'pong !'], 200);
    }
}
