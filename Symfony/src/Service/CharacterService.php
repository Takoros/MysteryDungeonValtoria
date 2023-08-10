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
use Exception;

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
    public function persistNewCharacter(Character $character, User $user){

        /**
         * Verify that user does not already have a character
         */
        if($user->getCharacter() !== null){
            throw new Exception("L'utilisateur possède déjà un personnage.");
        }

        /**
         * Verify and Add Name
         */
        if(preg_match('~[0-9]+~', $character->getName())){
            throw new Exception("Le nom ne peut contenir de numéros");
        }

        /**
         * Verify and Add Gender
         */
        if(!$character->getGender() && ($character->getGender() !== Character::GENDER_MALE && $character->getGender() !== Character::GENDER_FEMALE)){
            throw new Exception("Le genre n'est pas défini, ou incorrect.");
        }

        /**
         * Verify and Add Age
         */
        if(!$character->getAge() && ($character->getAge() < 18 || $character->getAge() > 44)){
            throw new Exception("L'âge n'est pas défini, ou incorrect.");
        }

        /**
         * Verify and Add Species
         */
        if(!$character->getSpecies() || !$character->getSpecies()->isIsPlayable()){
            throw new Exception("L'espèce n'est pas défini, ou incorrect.");
        }

        /**
         * Add the Rotations
         */
        $lutte = $this->attackRepository->find("ATTACK_EXPLORER_BASE");

        $openerRotation = new Rotation();
        $openerRotation->setCharacter($character)
                       ->setType(Rotation::TYPE_OPENER)
                       ->setAttackOne($lutte)
                       ->setAttackTwo($lutte)
                       ->setAttackThree($lutte)
                       ->setAttackFour($lutte)
                       ->setAttackFive($lutte);

        $rotation = new Rotation();
        $rotation->setCharacter($character)
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
        $timers->setRaidCharges(1)
               ->setDungeonCharges(1);
        $this->entityManager->persist($timers);

        $character->setLevel(1)
                  ->setXP(0)
                  ->setStatPoints(5)
                  ->setDescription('')
                  ->setRank(0)
                  ->setUserI($user)
                  ->setStats($newStats)
                  ->setTimers($timers)
                  ->setIsShiny(false);

        $user->addRoles(['ROLE_CHARACTER']);

        $this->entityManager->persist($character);
        $this->entityManager->flush();

        return $character;
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
                'message' => "La nouvelle description est incorrecte"
            ];
        }

        $this->entityManager->flush();

        return [
            'statusCode' => 200,
            'message' => "Description modifiée."
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
                'message' => "La statistique à augmenter est incorrecte."
            ];
        }

        if($character->getStatPoints() >= $amount ){
            for ($i=0; $i < $amount; $i++) { 
                $newStatValue = $character->getStats()->increaseStat($statToModify);
                $character->setStatPoints($character->getStatPoints() - 1);
            }

            $this->entityManager->flush();
            return [
                'statusCode' => 200,
                'message' => "Statistique augmentée.",
                'newStatValue' => $newStatValue,
                'newStatPointsValue' => $character->getStatPoints() 
            ];
        }
        else {
            return [
                'statusCode' => 400,
                'message' => "Vous n'avez pas assez de point de statistique."
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
                'message' => 'Mauvais type de rotation'
            ];
        }

        $Attack = $this->attackRepository->findOneBy(['name' => $newAttackName]);
        $AvailableAttacks = $this->getAvailableAttacks($character);

        if($Attack === null){
            return [
                'statusCode' => 400,
                'message' => 'Attaque introuvable.'
            ];
        }
        else if(!in_array($Attack, $AvailableAttacks)){
            return [
                'statusCode' => 400,
                'message' => "Attaque non disponible pour ce personnage."
            ];
        }

        if($rotation->canFitAttackIntoSlot($Attack, $character->getStats()->getActionPoint(), $attackSlot) ){
            $rotation->setSlotAttack($attackSlot, $Attack);
            $this->entityManager->flush();
            return [
                'statusCode' => 200,
                'message' => 'Attaque modifiée.'
            ];
        }
        else {
            return [
                'statusCode' => 400,
                'message' => "Vous n'avez pas assez de PA restant pour ajouter cette attaque sur cet emplacement."
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
