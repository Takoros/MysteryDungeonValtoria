<?php

namespace App\Command;

use App\Entity\Species;
use App\Entity\Type;
use App\Repository\SpeciesRepository;
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

use function PHPSTORM_META\type;

#[AsCommand(
    name: 'app:create_types_species',
    description: 'Creates Types and Species in the database.',
)]
class CreateTypesSpeciesCommand extends Command
{
    private $entityManager;
    private $speciesRepository;
    private $typeRepository;
    private $kernel;

    public function __construct(ManagerRegistry $doctrine, SpeciesRepository $speciesRepository, TypeRepository $typeRepository, KernelInterface $kernel)
    {
        $this->entityManager = $doctrine->getManager();
        $this->speciesRepository = $speciesRepository;
        $this->typeRepository = $typeRepository;
        $this->kernel = $kernel;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $speciesListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . '/src/Data/species.json'));   
        $typeListJson = json_decode(file_get_contents($this->kernel->getProjectDir() . '/src/Data/types.json'));
        $speciesListDatabase = $this->speciesRepository->findAll();
        $typeListDatabase = $this->typeRepository->findAll();

        /**
         * Verifies the Types in Database and apply correct data if necessary
         */
        foreach ($typeListJson as $typeJsonId => $typeJson) {
            $doExist = false;

            // Verify if the type exist in database
            foreach ($typeListDatabase as $typeDatabase) {
                if((int) $typeJsonId === (int) $typeDatabase->getId()){
                    $doExist = true;
                    // if type exist, verify its content

                    if( $typeJson->name !== $typeDatabase->getName()){
                        // Corrects the name
                        $typeDatabase->setName($typeJson->name);
                    }        

                    if($typeJson->attackFile !== $typeDatabase->getAttackFile()){
                        // Corrects the attackFile
                        $typeDatabase->setAttackFile($typeJson->attackFile);
                    }
                }
            }

            // In case it doesnt exist in database, create it.
            if(!$doExist){
                $newType = new Type();
                $newType->setId($typeJsonId)
                        ->setName($typeJson->name)
                        ->setAttackFile($typeJson->attackFile);

                $this->entityManager->persist($newType);
            }
        }

        /**
         * Verifies every type in database exist in the json
         */
        foreach($typeListDatabase as $typeDatabase){
            if(!property_exists($typeListJson, $typeDatabase->getId())){
                $this->entityManager->remove($typeDatabase);
            }
        }

        /**
         * Verifies the Species in Database and apply correct data if necessary
         */
        foreach ($speciesListJson as $speciesJsonId => $speciesJson) {
            $doExist = false;

            // Verify if the species exist in database
            foreach ($speciesListDatabase as $speciesDatabase) {
                if((int) $speciesJsonId === (int) $speciesDatabase->getId()){
                    $doExist = true;
                    // if species exist, verify its content

                    if( $speciesJson->name !== $speciesDatabase->getName()){
                        // Corrects the name
                        $speciesDatabase->setName($speciesJson->name);
                    }

                    if( $speciesJson->isPlayable !== $speciesDatabase->isIsPlayable()){
                        // Corrects the isPlayable
                        $speciesDatabase->setIsPlayable($speciesJson->isPlayable);
                    }

                    if( $speciesJson->types !== $speciesDatabase->getType()){
                        // Corrects the Types

                        // Add missing types
                        foreach ($speciesJson->types as $typeId) {
                            $type = $this->typeRepository->find($typeId);
                            if(!$speciesDatabase->getType()->contains($type)){
                                $speciesDatabase->addType($type);
                            }
                        }

                        // Remove the extra types
                        foreach ($speciesDatabase->getType() as $type){
                            if(!in_array($type->getId(), $speciesJson->types)){
                                $speciesDatabase->removeType($type);
                            }
                        }
                    }
                }
            }

            if(!$doExist){
                $newSpecies = new Species();
                $newSpecies->setId($speciesJsonId)
                           ->setName($speciesJson->name)
                           ->setIsPlayable($speciesJson->isPlayable);

                foreach ($speciesJson->types as $typeId) {
                    $type = $this->typeRepository->find($typeId);
                    $newSpecies->addType($type);
                }
                $this->entityManager->persist($newSpecies);
            }
        }

        /**
         * Verifies every species in database exist in the json
         */
        foreach($speciesListDatabase as $speciesDatabase){
            if(!property_exists($speciesListJson, $speciesDatabase->getId())){
                $this->entityManager->remove($speciesDatabase);
            }
        }

        $this->entityManager->flush();
        return Command::SUCCESS;
    }
}
