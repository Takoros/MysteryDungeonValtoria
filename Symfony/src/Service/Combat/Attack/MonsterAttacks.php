<?php

namespace App\Service\Combat\Attack;

use App\Entity\Attack;
use App\Service\Combat\Fighter;
use App\Service\Combat\Status\StatusInterface;

trait MonsterAttacks
{
    use AbstractAttacks;

    /** @var Attack Débris Protecteurs */
    public Attack $ATTACK_MONSTER_ONE;

    /** @var Attack Cri Perçant */
    public Attack $ATTACK_MONSTER_TWO;

    public function loadMonsterAttacks() {
        $this->ATTACK_MONSTER_ONE = $this->attackRepository->find('ATTACK_MONSTER_ONE');
        $this->ATTACK_MONSTER_TWO = $this->attackRepository->find('ATTACK_MONSTER_TWO');
    }

    /**
     * Débris Protecteurs : Se roule dans les débris alentours pour augmenter son endurance et son courage pendant 4 tours.
     */
    public function ATTACK_MONSTER_ONE(Fighter &$caster, Fighter &$target): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $target, false);
        $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_BUFF, ['stamina', 'bravery'], 4, $caster, $target);

    }

    /**
     * Cri Perçant : Pousse un cri perçant les tympans des pokémons adverses, réduisant leur coordination et leur endurance pendant 3 tours.
     */
    public function ATTACK_MONSTER_TWO(Fighter &$caster, array &$targets): void
    {
        $ATTACK = __FUNCTION__;

        $attackDetails = $this->makeAttack($this->$ATTACK, $caster, $targets, false);

        foreach ($targets as $target) {
            $hasDodged = $this->hasDodged($caster, $target);

            if(!$hasDodged){
                $this->inflictStatisticModifier($this->$ATTACK, $attackDetails, StatusInterface::TYPE_NERF, ['coordination', 'stamina'], 3, $caster, $target);
            }
        }
    }
}