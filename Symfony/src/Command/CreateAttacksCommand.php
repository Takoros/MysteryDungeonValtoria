<?php

namespace App\Command;

use App\Entity\Attack;
use App\Repository\AttackRepository;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:create_attacks',
    description: 'Add attacks in the database',
)]
class CreateAttacksCommand extends Command
{
    const BUG_ATTACKS_FILE = '/src/Data/attacks/bug_attacks.json';
    const POISON_ATTACKS_FILE = '/src/Data/attacks/poison_attacks.json';
    const GHOST_ATTACKS_FILE = '/src/Data/attacks/ghost_attacks.json';
    const DARK_ATTACKS_FILE = '/src/Data/attacks/dark_attacks.json';
    const FLYING_ATTACKS_FILE = '/src/Data/attacks/flying_attacks.json';
    const STEEL_ATTACKS_FILE = '/src/Data/attacks/steel_attacks.json';
    const GROUND_ATTACKS_FILE = '/src/Data/attacks/ground_attacks.json';
    const ROCK_ATTACKS_FILE = '/src/Data/attacks/rock_attacks.json';
    const WATER_ATTACKS_FILE = '/src/Data/attacks/water_attacks.json';
    const FIRE_ATTACKS_FILE = '/src/Data/attacks/fire_attacks.json';
    const EXPLORER_ATTACKS_FILE = '/src/Data/attacks/explorer_attacks.json';
    const GRASS_ATTACKS_FILE = '/src/Data/attacks/grass_attacks.json';
    const ELECTRIC_ATTACKS_FILE = '/src/Data/attacks/electric_attacks.json';
    const NORMAL_ATTACKS_FILE = '/src/Data/attacks/normal_attacks.json';
    const ICE_ATTACKS_FILE = '/src/Data/attacks/ice_attacks.json';
    const FIGHTING_ATTACKS_FILE = '/src/Data/attacks/fighting_attacks.json';
    const PSY_ATTACKS_FILE = '/src/Data/attacks/psy_attacks.json';
    const FAIRY_ATTACKS_FILE = '/src/Data/attacks/fairy_attacks.json';
    const DRAGON_ATTACKS_FILE = '/src/Data/attacks/dragon_attacks.json';

    private $entityManager;
    private $attackRepository;
    private $typeRepository;
    private $kernel;

    public function __construct(ManagerRegistry $doctrine, AttackRepository $attackRepository, TypeRepository $typeRepository, KernelInterface $kernel)
    {
        $this->entityManager = $doctrine->getManager();
        $this->attackRepository = $attackRepository;
        $this->typeRepository = $typeRepository;
        $this->kernel = $kernel;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $bugAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::BUG_ATTACKS_FILE));
        $poisonAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::POISON_ATTACKS_FILE));
        $ghostAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::GHOST_ATTACKS_FILE));
        $darkAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::DARK_ATTACKS_FILE));
        $flyingAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::FLYING_ATTACKS_FILE));
        $steelAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::STEEL_ATTACKS_FILE));
        $groundAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::GROUND_ATTACKS_FILE));
        $rockAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::ROCK_ATTACKS_FILE));
        $waterAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::WATER_ATTACKS_FILE));
        $fireAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::FIRE_ATTACKS_FILE));
        $explorerAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::EXPLORER_ATTACKS_FILE));
        $grassAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::GRASS_ATTACKS_FILE));
        $electricAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::ELECTRIC_ATTACKS_FILE));
        $normalAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::NORMAL_ATTACKS_FILE));
        $iceAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::ICE_ATTACKS_FILE));
        $fightingAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::FIGHTING_ATTACKS_FILE));
        $psyAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::PSY_ATTACKS_FILE));
        $fairyAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::FAIRY_ATTACKS_FILE));
        $dragonAttackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . self::DRAGON_ATTACKS_FILE));

        $attackListJson = (object) array_merge((array) $bugAttackListJson,
                                               (array) $poisonAttackListJson,
                                               (array) $ghostAttackListJson,
                                               (array) $darkAttackListJson,
                                               (array) $flyingAttackListJson,
                                               (array) $steelAttackListJson,
                                               (array) $groundAttackListJson,
                                               (array) $rockAttackListJson,
                                               (array) $waterAttackListJson,
                                               (array) $fireAttackListJson,
                                               (array) $explorerAttackListJson,
                                               (array) $grassAttackListJson,
                                               (array) $electricAttackListJson,
                                               (array) $normalAttackListJson,
                                               (array) $iceAttackListJson,
                                               (array) $fightingAttackListJson,
                                               (array) $psyAttackListJson,
                                               (array) $fairyAttackListJson,
                                               (array) $dragonAttackListJson);

        $attackListDatabase = $this->attackRepository->findAll();

        /**
         * Verifies the Attack in Database and apply correct data if necessary
         */
        foreach ($attackListJson as $attackJsonId => $attackJson) {
            $doExist = false;

            // Verify if the attack exist in database
            foreach ($attackListDatabase as $attackDatabase) {
                if($attackJsonId === $attackDatabase->getId()){
                    $doExist = true;
                    // if attack exist, verify its content

                    if($attackJson->name !== $attackDatabase->getName()){
                        // Corrects the name
                        $attackDatabase->setName($attackJson->name);
                    }

                    $attackJsonTypeEntity = $this->typeRepository->findOneBy(['name' => $attackJson->type]);

                    if($attackJsonTypeEntity !== $attackDatabase->getType()){
                        // Corrects the type
                        $attackDatabase->setType($attackJsonTypeEntity);
                    }

                    if($attackJson->description !== $attackDatabase->getDescription()){
                        // Corrects the description
                        $attackDatabase->setDescription($attackJson->description);
                    }

                    if($attackJson->power !== $attackDatabase->getPower()){
                        // Corrects the power
                        $attackDatabase->setPower($attackJson->power);
                    }

                    if($attackJson->status_power !== $attackDatabase->getStatusPower()){
                        // Corrects the status power
                        $attackDatabase->setStatusPower($attackJson->status_power);
                    }

                    if($attackJson->critical_power !== $attackDatabase->getCriticalPower()){
                        // Corrects the critical power
                        $attackDatabase->setCriticalPower($attackJson->critical_power);
                    }

                    if($attackJson->action_point_cost !== $attackDatabase->getActionPointCost()){
                        // Corrects the action point cost
                        $attackDatabase->setActionPointCost($attackJson->action_point_cost);
                    }

                    if($attackJson->scope !== $attackDatabase->getScope()){
                        // Corrects the scope
                        $attackDatabase->setScope($attackJson->scope);
                    }

                    if($attackJson->level_required !== $attackDatabase->getLevelRequired()){
                        // Corrects the level required
                        $attackDatabase->setLevelRequired($attackJson->level_required);
                    }
                }
            }

            if(!$doExist){
                $attackJsonTypeEntity = $this->typeRepository->findOneBy(['name' => $attackJson->type]);

                $newAttack = new Attack();
                $newAttack->setId($attackJsonId)
                          ->setName($attackJson->name)
                          ->setType($attackJsonTypeEntity)
                          ->setDescription($attackJson->description)
                          ->setPower($attackJson->power)
                          ->setStatusPower($attackJson->status_power)
                          ->setCriticalPower($attackJson->critical_power)
                          ->setActionPointCost($attackJson->action_point_cost)
                          ->setScope($attackJson->scope)
                          ->setLevelRequired($attackJson->level_required);

                $this->entityManager->persist($newAttack);
            }
        }

        /**
         * Verifies every attack in the database exist in the json
         */
        foreach ($attackListDatabase as $attackDatabase) {
            if(!property_exists($attackListJson, $attackDatabase->getId())){
                $this->entityManager->remove($attackDatabase);
            }
        }

        $this->entityManager->flush();
        return Command::SUCCESS;
    }
}