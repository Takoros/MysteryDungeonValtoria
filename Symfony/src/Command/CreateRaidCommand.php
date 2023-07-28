<?php

namespace App\Command;

use App\Entity\Raid;
use App\Repository\AreaRepository;
use App\Repository\RaidRepository;
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
    name: 'app:create_raids',
    description: 'Add Raids in the database',
)]
class CreateRaidCommand extends Command
{
    const RAIDS_FOLDER = '/src/Data/Raids';

    private $entityManager;
    private $raidRepository;
    private $areaRepository;
    private $kernel;

    public function __construct(ManagerRegistry $doctrine, RaidRepository $raidRepository, AreaRepository $areaRepository, KernelInterface $kernel)
    {
        $this->entityManager = $doctrine->getManager();
        $this->raidRepository = $raidRepository;
        $this->areaRepository = $areaRepository;
        $this->kernel = $kernel;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $raidListDatabase = $this->raidRepository->findAll();
        $raidsDirectories = glob($this->kernel->getProjectDir() . self::RAIDS_FOLDER . '/*' , GLOB_ONLYDIR);
        $raidListJson = [];

        // Gets data for all raids in the jsons
        foreach ($raidsDirectories as $raidDirectory) {
            $raidId = basename($raidDirectory);
            $raidConfigFile = $raidId . '.json';

            $raidData =  json_decode(file_get_contents($raidDirectory . '/' . $raidConfigFile));
            $raidListJson[$raidId] = $raidData;
        }

        $raidListJson = (object) $raidListJson;

        /**
         * Verifies the Raids in database and apply correct data if necessary
         */
        foreach ($raidListJson as $raidJsonId => $raidJson) {
            $doExist = false;

            foreach ($raidListDatabase as $raidDatabase) {
                if($raidJsonId === $raidDatabase->getId()){
                    $doExist = true;
                    // If raid exist, verify its content
                }

                if($raidJson->name !== $raidDatabase->getName()){
                    // Corrects the name
                    $raidDatabase->setName($raidJson->name);
                }

                $areaJsonEntity = $this->areaRepository->findOneBy(['name' => $raidJson->area]);

                if($areaJsonEntity !== $raidDatabase->getArea()){
                    // Corrects the area
                    $raidDatabase->setArea($areaJsonEntity);
                }

                if($raidJson->enterMinLevel !== $raidDatabase->getEnterMinLevel()){
                    // Corrects the enterMinLevel
                    $raidDatabase->setEnterMinLevel($raidJson->enterMinLevel);
                }

                if($raidJson->roomNumbers !== $raidDatabase->getRoomNumbers()){
                    // Corrects the roomNumbers
                    $raidDatabase->setRoomNumbers($raidJson->roomNumbers);
                }

                if($raidJson->description !== $raidDatabase->getDescription()){
                    // Corrects the description
                    $raidDatabase->setDescription($raidJson->description);
                }

                if($raidJson->rooms !== $raidDatabase->getRooms()){
                    // Corrects the rooms
                    $raidDatabase->setRooms((array) $raidJson->rooms);
                }
            }

            if(!$doExist){
                $areaJsonEntity = $this->areaRepository->findOneBy(['name' => $raidJson->area]);

                $newRaid = new Raid();
                $newRaid->setId($raidJsonId)
                        ->setName($raidJson->name)
                        ->setArea($areaJsonEntity)
                        ->setEnterMinLevel($raidJson->enterMinLevel)
                        ->setRoomNumbers($raidJson->roomNumbers)
                        ->setDescription($raidJson->description)
                        ->setRooms((array) $raidJson->rooms);

                $this->entityManager->persist($newRaid);
            }
        }

        /**
         * Verifies every Raid in database exist in the json
         */
        foreach ($raidListDatabase as $raidDatabase) {
            if(!property_exists($raidListJson, $raidDatabase->getId())){
                $this->entityManager->remove($raidDatabase);
            }
        }

        $this->entityManager->flush();
        return Command::SUCCESS;
    }
}
