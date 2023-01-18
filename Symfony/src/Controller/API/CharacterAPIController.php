<?php

namespace App\Controller\API;

use App\Formatter\CharacterFormatter;
use App\Repository\CharacterRepository;
use App\Repository\UserRepository;
use App\Service\APIService;
use App\Service\CharacterService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CharacterAPIController extends AbstractAPIController
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
        $post = json_decode($request->getContent());
        $isValid = $this->verifyTokenAndData($post, ["discordUserId","characterName","characterGender","characterAge","characterSpeciesId"], $this->apiService);

        if(!is_bool($isValid)){
            return $isValid;
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
        $post = json_decode($request->getContent());

        $isValid = $this->verifyTokenAndData($post, ["discordUserId"], $this->apiService);

        if(!is_bool($isValid)){
            return $isValid;
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
        $post = json_decode($request->getContent());

        $isValid = $this->verifyTokenAndData($post, ["discordUserId", "description"], $this->apiService);

        if(!is_bool($isValid)){
            return $isValid;
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
        $post = json_decode($request->getContent());

        $isValid = $this->verifyTokenAndData($post, ["discordUserId", "statToIncrease"], $this->apiService);

        if(!is_bool($isValid)){
            return $isValid;
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
