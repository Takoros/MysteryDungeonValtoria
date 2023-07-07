<?php

namespace App\Controller\API;

use App\Entity\Character;
use App\Entity\Dungeon;
use App\Entity\DungeonInstance;
use App\Entity\User;
use App\Formatter\CharacterFormatter;
use App\Repository\AttackRepository;
use App\Repository\DungeonRepository;
use App\Repository\SpeciesRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Service\APIService;
use App\Service\CharacterService;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AbstractAPIController extends AbstractController
{
    public const ERROR_USER_NOT_FOUND = 'error-user-not-found';
    public const ERROR_USER_CHARACTER_NOT_FOUND = 'error-user-character-not-found';
    public const ERROR_DUNGEON_INSTANCE_FIGHT_NO_MONSTERS = 'error-dungeon-instance-fight-no-monsters';
    public const ERROR_CHARACTER_NOT_LEADER_DUNGEON_INSTANCE = 'error-character-not-leader-dungeon-instance';
    public const ERROR_DUNGEON_INSTANCE_NOT_EXPLORATION_PHASE = 'error-dungeon-instance-not-exploration-phase';
    public const ERROR_DUNGEON_INSTANCE_NOT_PREPARATION_PHASE = 'error-dungeon-instance-not-preparation-phase';

    /* Request & stuff */
    public stdClass $post;
    public Request $request;
    public bool|JsonResponse $isValid;

    /* Services */
    public APIService $apiService;
    public CharacterService $characterService;
    public CharacterFormatter $characterFormatter;

    /* Repositories */
    public UserRepository $userRepository;
    public TypeRepository $typeRepository;
    public AttackRepository $attackRepository;
    public SpeciesRepository $speciesRepository;
    public DungeonRepository $dungeonRepository;

    public function __construct(RequestStack $requestStack, APIService $apiService, CharacterService $characterService, CharacterFormatter $characterFormatter,
                                UserRepository $userRepository, DungeonRepository $dungeonRepository, TypeRepository $typeRepository, AttackRepository $attackRepository,
                                SpeciesRepository $speciesRepository)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->post = json_decode($this->request->getContent());

        /* Services */
        $this->apiService = $apiService;
        $this->characterService = $characterService;
        $this->characterFormatter = $characterFormatter;
        
        /* Repositories */
        $this->userRepository = $userRepository;
        $this->typeRepository = $typeRepository;
        $this->attackRepository = $attackRepository;
        $this->speciesRepository = $speciesRepository;
        $this->dungeonRepository = $dungeonRepository;

        $route = $this->request->attributes->get('_route');
        $params = $this->getApiRouteParams($route);

        $this->isValid = $this->verifyTokenAndData($this->post, $params);
    }

    public function verifyTokenAndData($post, $awaitedData){
        if(empty($post->token) || !$this->apiService->isCorrectToken($post->token)){
            return new JsonResponse(['message' => 'Token API Incorrect.'], 401);
        }

        if(empty($post->data) || !$this->apiService->hasCorrectData($awaitedData, $post->data)){
            return new JsonResponse(['message' => 'Arguments manquants pour cette route.'], 400);
        }

        return true;
    }

    /**
     * Returns the API Message For an error.
     */
    public function getJsonResponseForError(string $errorType): JsonResponse
    {
        switch ($errorType) {
            case self::ERROR_USER_CHARACTER_NOT_FOUND:
                $jsonResponse = new JsonResponse(['message' => "L'utilisateur n'a pas de personnage."], 400);
            case self::ERROR_USER_NOT_FOUND:
                $jsonResponse = new JsonResponse(['message' => "L'utilisateur n'existe pas."], 400);
                break;
            case self::ERROR_CHARACTER_NOT_LEADER_DUNGEON_INSTANCE:
                $jsonResponse = new JsonResponse(['message' => "Vous n'êtes pas le leader du donjon."], 400);
                break;
            case self::ERROR_DUNGEON_INSTANCE_NOT_EXPLORATION_PHASE:
                $jsonResponse = new JsonResponse(['message' => "L'instance n'est pas en phase d'exploration."], 400);
                break;
            case self::ERROR_DUNGEON_INSTANCE_NOT_PREPARATION_PHASE:
                $jsonResponse = new JsonResponse(['message' => "L'instance n'est pas en phase d'exploration."], 400);
                break;
            case self::ERROR_DUNGEON_INSTANCE_FIGHT_NO_MONSTERS:
                $jsonResponse = new JsonResponse(['message' => "Aucun monstre n'est présent sur la case."], 400);
                break;
            default:
                $jsonResponse = new JsonResponse(['message' => 'Erreur par défaut, veuillez contacter un administrateur.'], 500);
                break;
        }

        return $jsonResponse;
    }

    /* -------------------------------------------------------------------------- */
    /*                                 API GETTERS                                */
    /* -------------------------------------------------------------------------- */

    public function apiGetUserByDiscordTag(string $discordTag): User|JsonResponse
    {
        $user = $this->userRepository->findOneBy(['discordTag' => $discordTag]);

        if($user === null){
            return $this->getJsonResponseForError(self::ERROR_USER_NOT_FOUND);
        }

        return $user;
    }

    public function apiGetCharacterByUser(User $user): Character|JsonResponse
    {
        $character = $user->getCharacter();

        if($character === null){
            return $this->getJsonResponseForError(self::ERROR_USER_CHARACTER_NOT_FOUND);
        }

        return $character;
    }

    public function apiGetDungeonByName(string $name): Dungeon|JsonResponse
    {
        $dungeon = $this->dungeonRepository->findOneBy(['name' => $this->post->data->dungeonName]);

        if($dungeon === null){
            return new JsonResponse(['message' => "Ce donjon n'existe pas."], 400);
        }

        return $dungeon;
    }

    public function apiGetDungeonInstanceByCharacter(Character $character): DungeonInstance|JsonResponse
    {
        $dungeonInstance = $character->getCurrentExplorationDungeonInstance();

        if($dungeonInstance === null){
            return new JsonResponse(['message' => "Vous n'êtes pas dans un donjon."], 400);
        }

        return $dungeonInstance;
    }

    /* -------------------------------------------------------------------------- */
    /*                              PRIVATE FUNCTIONS                             */
    /* -------------------------------------------------------------------------- */

    /**
     * Returns the args for the current route
     */
    private function getApiRouteParams(string $route): array
    {
        $argsProperty = strtoupper($route).'_ARGS';

        return $this->$argsProperty;
    }
}
