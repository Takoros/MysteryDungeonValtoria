<?php

namespace App\Command;

use App\Repository\CharacterRepository;
use Doctrine\ORM\EntityManager;
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
    name: 'app:refill_raid_charges',
    description: 'Gives a charge of raid to every player',
)]
class RefillRaidChargesCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private CharacterRepository $characterRepository;

    public function __construct(ManagerRegistry $doctrine, CharacterRepository $characterRepository)
    {
        $this->entityManager = $doctrine->getManager();
        $this->characterRepository = $characterRepository;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $allExplorers = $this->characterRepository->findAll();
        
        foreach ($allExplorers as $explorer) {
            $explorerTimer = $explorer->getTimers();

            $explorerTimer->addOneRaidCharge();
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
