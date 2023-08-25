<?php

namespace App\Command;

use App\Entity\Gear;
use App\Entity\Inventory;
use App\Repository\CharacterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-inventory-gear',
    description: 'Adds Inventories and Gear to existing Characters',
)]
class AddInventoryGearToExistingCharacterCommand extends Command
{
    private EntityManagerInterface $em;
    private CharacterRepository $characterRepository;

    public function __construct(ManagerRegistry $doctrine, CharacterRepository $characterRepository)
    {
        $this->em = $doctrine->getManager();
        $this->characterRepository = $characterRepository;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $allExplorers = $this->characterRepository->findAll();
        
        foreach ($allExplorers as $explorer) {
            $Gear = new Gear();
            $Gear->initNewGear($this->em);
    
            $Inventory = new Inventory();
            $Inventory->initNewInventory();

            $this->em->persist($Gear);
            $this->em->persist($Inventory);

            $explorer->setGear($Gear)
            ->setInventory($Inventory);
        }

        $this->em->flush();

        return Command::SUCCESS;
    }
}
