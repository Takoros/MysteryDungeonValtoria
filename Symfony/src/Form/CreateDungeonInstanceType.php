<?php

namespace App\Form;

use App\Entity\Character;
use App\Entity\Dungeon;
use App\Repository\DungeonRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateDungeonInstanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if(!array_key_exists('character', $options) || $options['character'] === null){
            throw new ParameterNotFoundException('Paramètre character nécessaire au formulaire');
        }

        if(!array_key_exists('dungeonRepository', $options) || $options['dungeonRepository'] === null){
            throw new ParameterNotFoundException('Paramètre dungeonRepository nécessaire au formulaire');
        }

        $character = $options['character'];
        $dungeonRepository = $options['dungeonRepository'];

        $builder
            ->add('Dungeon', EntityType::class, [
                'required' => true,
                'label' => ' ',
                'class' => Dungeon::class,
                'choices' => $character->getAvailableDungeons($dungeonRepository),
                'choice_label' => 'name'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Confirmer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'character' => null,
            'dungeonRepository' => null
        ]);

        $resolver->setAllowedTypes('character', Character::class);
        $resolver->setAllowedTypes('dungeonRepository', DungeonRepository::class);
    }
}
