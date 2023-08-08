<?php

namespace App\Command;

use App\Repository\UserRepository;
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
    name: 'app:add_character_roles',
    description: 'Gives Character Role to users that have a character',
)]
class AddCharacterRoleToUsersCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    public function __construct(ManagerRegistry $doctrine, UserRepository $userRepository)
    {
        $this->entityManager = $doctrine->getManager();
        $this->userRepository = $userRepository;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $allUsers = $this->userRepository->findAll();

        foreach ($allUsers as $user) {
            if($user->getCharacter() !== null){
                $user->addRoles(['ROLE_CHARACTER']);
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
