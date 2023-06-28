<?php

namespace App\Command;

use App\Entity\Area;
use App\Repository\AreaRepository;
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
    name: 'app:create_areas',
    description: 'Adds Areas in the database.',
)]
class CreateAreaCommand extends Command
{
    private $entityManager;
    private $areaRepository;
    private $kernel;

    public function __construct(ManagerRegistry $doctrine, AreaRepository $areaRepository, KernelInterface $kernel)
    {
        $this->entityManager = $doctrine->getManager();
        $this->areaRepository = $areaRepository;
        $this->kernel = $kernel;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $areaListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . '/src/Data/areas.json'));
        $areaListDatabase = $this->areaRepository->findAll();

        foreach ($areaListJson as $areaJsonId => $areaJson) {
            $doExist = false;

            // Verify if the area exist in database
            foreach ($areaListDatabase as $areaDatabase) {
                if((int) $areaJsonId === (int) $areaDatabase->getId()){
                    $doExist = true;
                    // if area exist, verify its content

                    if($areaJson->name !== $areaDatabase->getName()){
                        // Corrects the name
                        $areaDatabase->setName($areaJson->name);
                    }

                    if($areaJson->isExplorable !== $areaJson->isExplorable()){
                        // Corrects the isExplorable
                        $areaDatabase->setIsExplorable($areaJson->isExplorable);
                    }
                }
            }

            if(!$doExist){
                $newArea = new Area();
                $newArea->setName($areaJson->name)
                        ->setIsExplorable($areaJson->isExplorable);

                $this->entityManager->persist($newArea);
            }
        }

        /**
         * Verifies every area in database exist in the json
         */
        foreach($areaListDatabase as $areaDatabase){
            if(!property_exists($areaListJson, $areaDatabase->getId())){
                $this->entityManager->remove($areaDatabase);
            }
        }

        $this->entityManager->flush();
        return Command::SUCCESS;
    }
}
