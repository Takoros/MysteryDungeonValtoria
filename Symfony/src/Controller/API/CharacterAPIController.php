<?php

namespace App\Controller\API;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CharacterAPIController extends AbstractAPIController
{
    public array $API_CHARACTER_CREATE_ARGS = ["discordUserId", "characterName", "characterGender", "characterAge", "characterSpeciesName"];
    public array $API_CHARACTER_RESUME_ARGS = ["discordUserId"];
    public array $API_CHARACTER_MODIFY_DESCRIPTION_ARGS = ["discordUserId", "description"];
    public array $API_CHARACTER_SPEND_STATPOINT_ARGS = ["discordUserId", "statToIncrease", "amountOfPointsSpent"];
    public array $API_CHARACTER_MODIFY_ATTACK_ARGS = ["discordUserId", "rotationType", "attackSlot", "attackName"];
    public array $API_CHARACTER_RESUME_ROTATION_ARGS = ["discordUserId", "rotationType"];
    public array $API_CHARACTER_RESUME_AVAILABLE_ATTACKS_ARGS = ["discordUserId"];
    public array $API_CHARACTER_TOGGLE_SHINY_ARGS = ["discordUserId"];

    /**
     * Creates a new character on API request
     */
    #[Route('/api/character/create', name: 'api_character_create',  methods:["POST"])]
    public function create(): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $results = $this->characterService->persistNewCharacter($this->post->data);

        return new JsonResponse(['message' => $results['message']], $results['statusCode']);
    }

    /**
     * Responds a resume of the character of a user
     */
    #[Route('/api/character/resume', name: 'api_character_resume',  methods:["POST"])]
    public function resume(): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }

        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }

        return new JsonResponse($this->characterFormatter->formatCharacter($character));
    }

    /**
     * Modifies Character's description
     */
    #[Route('/api/character/modify/description', name: 'api_character_modify_description',  methods:["POST"])]
    public function modifyDescription(): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }
        
        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }

        $results = $this->characterService->modifyDescription($this->post->data->description, $character);

        return new JsonResponse(['message' => $results['message']], $results['statusCode']);
    }

    /**
     * Spend statPoints to increase stat
     */
    #[Route('/api/character/spend/statPoint', name: 'api_character_spend_statPoint',  methods:["POST"])]
    public function spendStatPoint(): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }
        
        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }

        $results = $this->characterService->spendStatPoint($character, $this->post->data->statToIncrease, $this->post->data->amountOfPointsSpent);

        return new JsonResponse(['message' => $results['message']], $results['statusCode']);
    }

    /**
     * Modify an attack slot
     */
    #[Route('/api/character/modify/attack', name: 'api_character_modify_attack',  methods:["POST"])]
    public function modifyRotationAttack( ): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }
        
        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }

        $results = $this->characterService->modifyRotationAttack($character, $this->post->data->rotationType, $this->post->data->attackSlot, $this->post->data->attackName);

        return new JsonResponse(['message' => $results['message']], $results['statusCode']);
    }

    /**
     * Responds a resume of a Rotation|Opener
     */
    #[Route('/api/character/resume/rotation', name: 'api_character_resume_rotation',  methods:["POST"])]
    public function resumeRotation(): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }
        
        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }

        return new JsonResponse($this->characterFormatter->formatRotation($character, $this->post->data->rotationType));
    }

    /**
     * Responds a resume of all the attacks available for a character
     */
    #[Route('/api/character/resume/available-attacks', name: 'api_character_resume_available_attacks',  methods:["POST"])]
    public function resumeAvailableAttacks(): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }
        
        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }

        $attackList = $this->characterService->getAvailableAttacks($character);
        $formattedAttackList = [];

        foreach ($attackList as $attack) {
            $formattedAttackList[] = $this->characterFormatter->formatAttack($attack);
        }

        return new JsonResponse($formattedAttackList);
    }

    /**
     * Modify an attack slot
     */
    #[Route('/api/character/toggle-shiny', name: 'api_character_toggle_shiny',  methods:["POST"])]
    public function toggleShiny(EntityManagerInterface $em): JsonResponse
    {
        if(!is_bool($this->isValid)){
            return $this->isValid;
        }

        $user = $this->apiGetUserByDiscordTag($this->post->data->discordUserId);

        if(get_class($user) === JsonResponse::class){
            return $user;
        }
        
        $character = $this->apiGetCharacterByUser($user);

        if(get_class($character) === JsonResponse::class){
            return $character;
        }

        $character->setIsShiny(!$character->isShiny());
        $em->flush();

        if($character->isShiny() === true){
            $message = 'Mode Shiny Activé';
        }
        else {
            $message = 'Mode Shiny désactivé';
        }

        return new JsonResponse(['message' => $message], 200);
    }
}
