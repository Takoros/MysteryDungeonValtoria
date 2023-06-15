<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Rotation;
use App\Entity\Stats;
use App\Entity\User;
use App\Repository\AttackRepository;
use App\Repository\SpeciesRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;

class CharacterService
{
    private $userRepository;
    private $speciesRepository;
    private $attackRepository;
    private $entityManager;

    public function __construct(UserRepository $userRepository, SpeciesRepository $speciesRepository, AttackRepository $attackRepository, ManagerRegistry $doctrine)
    {
        $this->userRepository = $userRepository;
        $this->speciesRepository = $speciesRepository;
        $this->attackRepository = $attackRepository;
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
        if($characterData->discordUserId && is_int($characterData->discordUserId)){
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
        if($characterData->characterGender && ($characterData->characterGender === "Mâle" || $characterData->characterGender === "Femelle")){
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
        if($characterData->characterSpeciesId){
            $species = $this->speciesRepository->find($characterData->characterSpeciesId);

            if($species !== null && $species->isIsPlayable()){
                $newCharacter->setSpecies($species);
            }
            else {
                return [
                    'statusCode' => 400,
                    'message' => "characterSpeciesId is not defined or incorrect."
                ];
            }
        }
        else {
            return [
                'statusCode' => 400,
                'message' => "characterSpeciesId is not defined or incorrect."
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

        $newCharacter->setLevel(1)
                     ->setXP(0)
                     ->setStatPoints(0)
                     ->setDescription('')
                     ->setRank(0)
                     ->setUserI($User)
                     ->setStats($newStats);

        $this->entityManager->persist($newCharacter);
        $this->entityManager->flush();

        return [
            'statusCode' => 201,
            'message' => "Character created."
        ];
    }

    /**
     * Fait monter de niveau le character
     */
    public function levelUp($character){
        if($character->hasEnoughXP()){
            $character->setXp($character->getXp() - $character->getXPCeil())
                      ->setStatPoints($character->getStatPoints() + 5)
                      ->setLevel($character->getLevel() + 1)
                      ->getStats()->increaseBaseStat(1);

            $this->entityManager->flush();
            return true;
        }

        return false;
    }
    
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

    public function spendStatPoint($character, $statToModify){
        $statModifyable = ['vitality', 'strength', 'stamina', 'power', 'bravery', 'presence', 'impassiveness', 'agility', 'coordination', 'speed'];

        if($character->getStatPoints() > 0){
            if($statToModify && in_array($statToModify, $statModifyable)){
                $character->getStats()->increaseStat($statToModify);
                $character->setStatPoints($character->getStatPoints() - 1);
                $this->entityManager->flush();

                return [
                    'statusCode' => 200,
                    'message' => "Stat increased."
                ];
            }           
            else {
                return [
                    'statusCode' => 400,
                    'message' => "Stat to increase is incorrect"
                ];
            } 
        }
        else {
            return [
                'statusCode' => 400,
                'message' => "Character does not have any statPoints"
            ];
        }
    }
}
