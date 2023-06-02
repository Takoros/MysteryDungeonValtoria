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
    description: 'Add a short description for your command',
)]
class CreateAttacksCommand extends Command
{
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
        $attackListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . '/src/Data/attacks.json'));
        $attackListDatabase = $this->attackRepository->findAll();

        /**
         * Verifies the Attack in Database and apply correct data if necessary
         */
        foreach ($attackListJson as $attackJsonId => $attackJson) {
            $doExist = false;

            // Verify if the attack exist in database
            foreach ($attackListDatabase as $attackDatabase) {
                if((int) $attackJsonId === (int) $attackDatabase->getId()){
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

                    if($attackJson->critical_power !== $attackDatabase->getCriticalPower()){
                        // Corrects the critical power
                        $attackDatabase->setCriticalPower($attackJson->critical_power);
                    }

                    if($attackJson->action_point_cost !== $attackDatabase->getActionPointCost()){
                        // Corrects the action point cost
                        $attackDatabase->setActionPointCost($attackJson->action_point_cost);
                    }

                    if($attackJson->scope !== $attackDatabase->getScope()){
                        $attackDatabase->setScope($attackJson->scope);
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
                          ->setCriticalPower($attackJson->critical_power)
                          ->setActionPointCost($attackJson->action_point_cost)
                          ->setScope($attackJson->scope);

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
