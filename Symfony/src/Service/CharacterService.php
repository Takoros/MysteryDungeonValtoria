<?php

namespace App\Service;

use App\Entity\Character;
use App\Entity\Stats;
use App\Entity\User;
use App\Repository\SpeciesRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;

class CharacterService
{
    private $userRepository;
    private $speciesRepository;
    private $entityManager;

    public function __construct(UserRepository $userRepository, SpeciesRepository $speciesRepository, ManagerRegistry $doctrine)
    {
        $this->userRepository = $userRepository;
        $this->speciesRepository = $speciesRepository;
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
}
