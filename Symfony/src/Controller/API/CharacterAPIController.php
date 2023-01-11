<?php

namespace App\Controller\API;

use App\Service\APIService;
use App\Service\CharacterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CharacterAPIController extends AbstractController
{
    private $apiService;
    private $characterService;

    public function __construct(APIService $apiService, CharacterService $characterService)
    {
        $this->apiService = $apiService;
        $this->characterService = $characterService;
    }

    /**
     * Creates a new character on API request
     */
    #[Route('/api/character/create', name: 'api_character_create',  methods:["POST"])]
    public function create(Request $request): JsonResponse
    {
        $awaitedData = [
            "discordUserId" => null,
            "characterName" => null,
            "characterGender" => null,
            "characterAge" => null,
            "characterSpeciesId" => null
        ];
        $post = json_decode($request->getContent());

        if(empty($post->token) || !$this->apiService->isCorrectToken($post->token)){
            return new JsonResponse(['message' => 'Unauthorized'], 401);
        }

        if(empty($post->data) || !$this->apiService->hasCorrectData($awaitedData, $post->data)){
            return new JsonResponse(['message' => 'Bad Request'], 400);
        }

        $results = $this->characterService->persistNewCharacter($post->data);

        return new JsonResponse(['message' => $results['message']], $results['statusCode']);
    }
}
