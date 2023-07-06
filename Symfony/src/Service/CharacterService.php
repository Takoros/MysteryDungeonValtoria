<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Rotation;
use App\Entity\Stats;
use App\Entity\Timers;
use App\Entity\User;
use App\Repository\AttackRepository;
use App\Repository\SpeciesRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use App\Service\Dungeon\MonsterCharacter;
use Doctrine\Persistence\ManagerRegistry;

class CharacterService
{
    private $userRepository;
    private $speciesRepository;
    private $attackRepository;
    private $typeRepository;
    private $entityManager;

    public function __construct(UserRepository $userRepository, SpeciesRepository $speciesRepository, AttackRepository $attackRepository, TypeRepository $typeRepository, ManagerRegistry $doctrine)
    {
        $this->userRepository = $userRepository;
        $this->speciesRepository = $speciesRepository;
        $this->attackRepository = $attackRepository;
        $this->typeRepository = $typeRepository;
        $this->entityManager = $doctrine->getManager();
    }

    /**
     * Creates a new character and persist it
     */
    public function persistNewCharacter($characterData){
        $newCharacter = new Character;
        $User = null;

        /**
         * Verify that user does not already have a character, add the user if it doesnt exist
         */
        if($characterData->discordUserId && is_string($characterData->discordUserId)){
            $users = $this->userRepository->findAll();
            $userExist = false;

            foreach ($users as $user) {
                if($user->getDiscordTag() == $characterData->discordUserId && $user->getCharacter() !== null){
                    $userExist = true;
                    return [
                        'statusCode' => 400,
                        'message' => "User already have a character."
                    ];
                }
                else if($user->getDiscordTag() == $characterData->discordUserId){
                    $User = $user;
                    $userExist = true;
                    break;
                }
            }

            if($userExist === false){
                $User = new User();
                $User->setDiscordTag($characterData->discordUserId);
                $this->entityManager->persist($User);
            }
        }
        else {
            return [
                'statusCode' => 400,
                'message' => "discordUserId is not defined or incorrect."
            ];
        }


        /**
         * Verify and Add Name
         */
        if($characterData->characterName && is_string($characterData->characterName) && strlen($characterData->characterName) <= 30
           && !preg_match('~[0-9]+~', $characterData->characterName)){
            $newCharacter->setName($characterData->characterName);
        }
        else {
            return [
                'statusCode' => 400,
                'message' => "characterName is not defined or incorrect."
            ];
        }

        /**
         * Verify and Add Gender
         */
        if($characterData->characterGender && ($characterData->characterGender === Character::GENDER_MALE || $characterData->characterGender === Character::GENDER_FEMALE)){
            $newCharacter->setGender($characterData->characterGender);
        }
        else {
            return [
                'statusCode' => 400,
                'message' => "characterGender is not defined or incorrect."
            ];
        }

        /**
         * Verify and Add Age
         */
        if($characterData->characterAge && $characterData->characterAge >= 18 && $characterData->characterAge <= 60){
            $newCharacter->setAge($characterData->characterAge);
        }
        else {
            return [
                'statusCode' => 400,
                'message' => "characterAge is not defined or incorrect."
            ];
        }

        /**
         * Verify and Add Species
         */
        if($characterData->characterSpeciesName){
            $species = $this->speciesRepository->findOneBy(['name' => $characterData->characterSpeciesName]);

            if($species !== null && $species->isIsPlayable()){
                $newCharacter->setSpecies($species);
            }
            else {
                return [
                    'statusCode' => 400,
                    'message' => "characterSpeciesName is not defined or incorrect."
                ];
            }
        }
        else {
            return [
                'statusCode' => 400,
                'message' => "characterSpeciesName is not defined or incorrect."
            ];
        }

        /**
         * Add the Rotations
         */
        $lutte = $this->attackRepository->find("ATTACK_EXPLORER_BASE");

        $openerRotation = new Rotation();
        $openerRotation->setCharacter($newCharacter)
                       ->setType(Rotation::TYPE_OPENER)
                       ->setAttackOne($lutte)
                       ->setAttackTwo($lutte)
                       ->setAttackThree($lutte)
                       ->setAttackFour($lutte)
                       ->setAttackFive($lutte);

        $rotation = new Rotation();
        $rotation->setCharacter($newCharacter)
        ->setType(Rotation::TYPE_ROTATION)
        ->setAttackOne($lutte)
        ->setAttackTwo($lutte)
        ->setAttackThree($lutte)
        ->setAttackFour($lutte)
        ->setAttackFive($lutte);

        $this->entityManager->persist($openerRotation);
        $this->entityManager->persist($rotation);

        // Generates base stats
        $newStats = new Stats();
        $newStats->setVitality(25)
                 ->setStrength(7)
                 ->setStamina(7)
                 ->setPower(7)
                 ->setBravery(7)
                 ->setPresence(7)
                 ->setImpassiveness(7)
                 ->setAgility(7)
                 ->setCoordination(7)
                 ->setSpeed(7)
                 ->setActionPoint(6);
        $this->entityManager->persist($newStats);

        // Timers
        $timers = new Timers();
        $this->entityManager->persist($timers);

        $newCharacter->setLevel(1)
                     ->setXP(0)
                     ->setStatPoints(5)
                     ->setDescription('')
                     ->setRank(0)
                     ->setUserI($User)
                     ->setStats($newStats)
                     ->setTimers($timers);

        $this->entityManager->persist($newCharacter);
        $this->entityManager->flush();

        return [
            'statusCode' => 201,
            'message' => "Character created."
        ];
    }

    /**
     * Makes a Character gain a level
     */
    public function levelUp($character){
        
        if($character->hasEnoughXP()){
            if($character->getLevel() >= Character::MAX_LEVEL){
                $character->setXp($character->getXPCeil());

                $this->entityManager->flush();
                return true;
            }

            $character->setXp($character->getXp() - $character->getXPCeil())
                      ->setStatPoints($character->getStatPoints() + 5)
                      ->setLevel($character->getLevel() + 1)
                      ->getStats()->increaseBaseStat(1);

            $this->entityManager->flush();
            return true;
        }

        return false;
    }
    
    /**
     * Modifies a Character's description
     */
    public function modifyDescription($newDescription, $character){

        /**
         * Verifies if the description is correct and not empty
         */
        if($newDescription && strlen($newDescription) <= 200){
            $character->setDescription($newDescription);
        }
        else {
            return [
                'statusCode' => 400,
                'message' => "New description is incorrect"
            ];
        }

        $this->entityManager->flush();

        return [
            'statusCode' => 201,
            'message' => "Description is modified."
        ];
    }

    /**
     * Makes a Character spend Stat Points
     */
    public function spendStatPoint(Character $character, string $statToModify, int $amount){
        $statModifyable = ['vitality', 'strength', 'stamina', 'power', 'bravery', 'presence', 'impassiveness', 'agility', 'coordination', 'speed'];

        if(!in_array($statToModify, $statModifyable)){
            return [
                'statusCode' => 400,
                'message' => "Stat to increase is incorrect"
            ];
        }

        if($character->getStatPoints() >= $amount ){
            for ($i=0; $i < $amount; $i++) { 
                $character->getStats()->increaseStat($statToModify);
                $character->setStatPoints($character->getStatPoints() - 1);
            }

            $this->entityManager->flush();
            return [
                'statusCode' => 200,
                'message' => "Stat increased."
            ];
        }
        else {
            return [
                'statusCode' => 400,
                'message' => "Character does not have enough statPoints"
            ];
        }
    }

    /**
     * Changes a Rotation|Opener's Character Attack
     */
    public function modifyRotationAttack(Character $character, string $rotationType, int $attackSlot, string $newAttackName): array
    {
        if($rotationType === Rotation::TYPE_OPENER){
            $rotation = $character->getOpenerRotation();
        }
        else if ($rotationType === Rotation::TYPE_ROTATION){
            $rotation = $character->getRotation();
        }
        else {
            return [
                'statusCode' => 400,
                'message' => 'Wrong Rotation Type'
            ];
        }

        $Attack = $this->attackRepository->findOneBy(['name' => $newAttackName]);
        $AvailableAttacks = $this->getAvailableAttacks($character);

        if($Attack === null){
            return [
                'statusCode' => 400,
                'message' => 'AttackId does not relate to any Attack'
            ];
        }
        else if(!in_array($Attack, $AvailableAttacks)){
            return [
                'statusCode' => 400,
                'message' => 'This Attack is not available for this character'
            ];
        }

        if($rotation->canFitAttackIntoSlot($Attack, $character->getStats()->getActionPoint(), $attackSlot) ){
            $rotation->setSlotAttack($attackSlot, $Attack);
            $this->entityManager->flush();
            return [
                'statusCode' => 200,
                'message' => 'Attack modified'
            ];
        }
        else {
            return [
                'statusCode' => 400,
                'message' => 'This Rotation does not have enough Action Point left to change this slot with this attack'
            ];
        }
    }

    /**
     * Returns in a array all the Attacks available for a character
     */
    public function getAvailableAttacks(Character|MonsterCharacter $character): array
    {
        $adventurerType = $this->typeRepository->findOneBy(['name' => 'Aventurier']);

        $allCharacterAttackTypes = [$adventurerType];

        foreach ($character->getTypes() as $type) {
            $allCharacterAttackTypes[] = $type;
        }

        $attackList = [];
        foreach ($allCharacterAttackTypes as $type) {
            foreach ($this->attackRepository->findAvailableAttacksForLevelAndType($character->getLevel(), $type) as $attack) {
                $attackList[] = $attack;
            }
        }
        
        return $attackList;
    }
}
