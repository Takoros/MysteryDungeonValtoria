<?php

namespace App\Service\Raid;

use App\Command\CreateRaidCommand;
use App\Repository\AttackRepository;
use App\Repository\SpeciesRepository;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;

class RaidDataManager
{
    public ?KernelInterface $kernel = null;
    public ?AttackRepository $attackRepository = null;
    public ?SpeciesRepository $speciesRepository = null;

    public ?string $raidId = null;

    public function __construct(KernelInterface $kernel, AttackRepository $attackRepository, SpeciesRepository $speciesRepository)
    {
        $this->kernel = $kernel;
        $this->attackRepository = $attackRepository;
        $this->speciesRepository = $speciesRepository;
    }

    public function getDataFolderPath(): string
    {
        return $this->kernel->getProjectDir() . CreateRaidCommand::RAIDS_FOLDER . '/' . $this->raidId;
    }
    
    public function getMonsterFolderPath(): string
    {
        return $this->getDataFolderPath() . '/' . 'Monsters'; 
    }

    public function setRaidId(string $raidId): self
    {
        $this->raidId = $raidId;

        return $this;
    }

    /* -------------------------------------------------------------------------- */
    /*                              SERVICE FUNCTION                              */
    /* -------------------------------------------------------------------------- */

    public function getRaidMonsterFromId($monsterId){
        $monsterFilePath = $this->getMonsterFolderPath() . '/' . $monsterId . '.json';

        $monsterData = json_decode(file_get_contents($monsterFilePath));

        if($monsterData === null){
            throw new Exception("Wrong MonsterId");
        }
        
        $monsterRaidCharacter = new MonsterRaidCharacter($monsterData, $this->speciesRepository, $this->attackRepository);

        return $monsterRaidCharacter;
    }
}