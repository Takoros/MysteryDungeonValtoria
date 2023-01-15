<?php

namespace App\Controller\API;

use App\Formatter\CharacterFormatter;
use App\Repository\CharacterRepository;
use App\Repository\UserRepository;
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
    private $characterFormatter;

    public function __construct(APIService $apiService, CharacterService $characterService, CharacterFormatter $characterFormatter)
    {
        $this->apiService = $apiService;
        $this->characterService = $characterService;
        $this->characterFormatter = $characterFormatter;
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

    /**
     * Responds a resume of the character of a user
     */
    #[Route('/api/character/resume', name: 'api_character_resume',  methods:["POST"])]
    public function resume(Request $request, UserRepository $userRepository): JsonResponse
    {
        $awaitedData = ["discordUserId" => null];
        $post = json_decode($request->getContent());

        if(empty($post->token) || !$this->apiService->isCorrectToken($post->token)){
            return new JsonResponse(['message' => 'Unauthorized'], 401);
        }

        if(empty($post->data) || !$this->apiService->hasCorrectData($awaitedData, $post->data)){
            return new JsonResponse(['message' => 'Bad Request'], 400);
        }

        $user = $userRepository->findOneBy(['discordTag' => $post->data->discordUserId]);

        if($user === null){
            return new JsonResponse(['message' => 'User does not exist']);
        }
        
        $character = $user->getCharacter();

        if($character === null){
            return new JsonResponse(['message' => 'User does not have Character']);
        }

        return new JsonResponse($this->characterFormatter->formatCharacter($character));
    }

    /**
     * Modifies Character's description
     */
    #[Route('/api/character/modify/description', name: 'api_character_modify_description',  methods:["POST"])]
    public function modifyDescription(Request $request, UserRepository $userRepository): JsonResponse
    {
        $awaitedData = [
            "discordUserId" => null,
            "description" => null
        ];
        $post = json_decode($request->getContent());

        if(empty($post->token) || !$this->apiService->isCorrectToken($post->token)){
            return new JsonResponse(['message' => 'Unauthorized'], 401);
        }

        if(empty($post->data) || !$this->apiService->hasCorrectData($awaitedData, $post->data)){
            return new JsonResponse(['message' => 'Bad Request'], 400);
        }

        $user = $userRepository->findOneBy(['discordTag' => $post->data->discordUserId]);

        if($user === null){
            return new JsonResponse(['message' => 'User does not exist']);
        }
        
        $character = $user->getCharacter();

        $results = $this->characterService->modifyDescription($post->data->description, $character);

        return new JsonResponse(['message' => $results['message']], $results['statusCode']);
    }

    /**
     * Spend statPoints to increase stat
     */
    #[Route('/api/character/spend/statPoint', name: 'api_character_spend_statPoint',  methods:["POST"])]
    public function spendStatPoint(Request $request, UserRepository $userRepository): JsonResponse
    {
        $awaitedData = [
            "discordUserId" => null,
            "statToIncrease" => null
        ];
        $post = json_decode($request->getContent());

        if(empty($post->token) || !$this->apiService->isCorrectToken($post->token)){
            return new JsonResponse(['message' => 'Unauthorized'], 401);
        }

        if(empty($post->data) || !$this->apiService->hasCorrectData($awaitedData, $post->data)){
            return new JsonResponse(['message' => 'Bad Request'], 400);
        }

        $user = $userRepository->findOneBy(['discordTag' => $post->data->discordUserId]);

        if($user === null){
            return new JsonResponse(['message' => 'User does not exist'], 400);
        }
        
        $character = $user->getCharacter();

        $results = $this->characterService->spendStatPoint($character, $post->data->statToIncrease);

        return new JsonResponse(['message' => $results['message']], $results['statusCode']);
    }
}
