<?php

namespace App\Command;

use App\Entity\Dungeon;
use App\Repository\AreaRepository;
use App\Repository\DungeonRepository;
use App\Repository\TypeRepository;
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
    name: 'app:create_dungeons',
    description: 'Creates Dungeons in the database',
)]
class CreateDungeonsCommand extends Command
{
    private $entityManager;
    private $dungeonRepository;
    private $areaRepository;
    private $kernel;

    public function __construct(ManagerRegistry $doctrine, DungeonRepository $dungeonRepository, AreaRepository $areaRepository, TypeRepository $typeRepository, KernelInterface $kernel)
    {
        $this->entityManager = $doctrine->getManager();
        $this->dungeonRepository = $dungeonRepository;
        $this->areaRepository = $areaRepository;
        $this->kernel = $kernel;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dungeonListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . '/src/Data/dungeons.json'));
        $dungeonListDatabase = $this->dungeonRepository->findAll();

        /**
         * Verifies the Dungeons in Database and apply correct data if necessary
         */
        foreach ($dungeonListJson as $dungeonJsonId => $dungeonJson) {
            $doExist = false;

            // Verify if the dungeon exist in database
            foreach ($dungeonListDatabase as $dungeonDatabase) {
                if($dungeonJsonId === $dungeonDatabase->getId()){
                    $doExist = true;

                    if($dungeonJson->name !== $dungeonDatabase->getName()){
                        // Corrects the name
                        $dungeonDatabase->setName($dungeonJson->name);
                    }

                    $areaJsonEntity = $this->areaRepository->findOneBy(['name' => $dungeonJson->area]);

                    if($areaJsonEntity !== $dungeonDatabase->getArea()){
                        // Corrects the area
                        $dungeonDatabase->setArea($areaJsonEntity);
                    }

                    if($dungeonJson->maxMonsterLevel !== $dungeonDatabase->getMaxMonsterLevel()){
                        // Corrects the maxMonsterLevel
                        $dungeonDatabase->setMaxMonsterLevel($dungeonJson->maxMonsterLevel);
                    }

                    if($dungeonJson->minMonsterLevel !== $dungeonDatabase->getMinMonsterLevel()){
                        // Corrects the minMonsterLevel
                        $dungeonDatabase->setMinMonsterLevel($dungeonJson->minMonsterLevel);
                    }

                    if($dungeonJson->size !== $dungeonDatabase->getSize()){
                        // Corrects the size
                        $dungeonDatabase->setSize($dungeonJson->size);
                    }

                    if($dungeonJson->monsterLivingList !== $dungeonDatabase->getMonsterLivingList()){
                        // Corrects the monster living list
                        $dungeonDatabase->setMonsterLivingList((array) $dungeonJson->monsterLivingList);
                    }
                }
            }

            // In case it doesnt exist in database, create it.
            if(!$doExist){
                $areaJsonEntity = $this->areaRepository->findOneBy(['name' => $dungeonJson->area]);

                $newDungeon = new Dungeon();
                $newDungeon->setId($dungeonJsonId)
                           ->setName($dungeonJson->name)
                           ->setArea($areaJsonEntity)
                           ->setMaxMonsterLevel($dungeonJson->maxMonsterLevel)
                           ->setMinMonsterLevel($dungeonJson->minMonsterLevel)
                           ->setSize($dungeonJson->size)
                           ->setMonsterLivingList((array) $dungeonJson->monsterLivingList);

                $this->entityManager->persist($newDungeon);
            }
        }

        /**
         * Verifies every Dungeon in database exist in the json
         */
        foreach ($dungeonListDatabase as $dungeonDatabase) {
            if(!property_exists($dungeonListJson, $dungeonDatabase->getId())){
                $this->entityManager->remove($dungeonDatabase);
            }
        }

        $this->entityManager->flush();
        return Command::SUCCESS;
    }
}
