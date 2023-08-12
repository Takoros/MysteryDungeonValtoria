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
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateDungeonInstanceType extends AbstractType
{
    public TranslatorInterface $translator;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if(!array_key_exists('character', $options) || $options['character'] === null){
            throw new ParameterNotFoundException('Paramètre character nécessaire au formulaire');
        }

        if(!array_key_exists('dungeonRepository', $options) || $options['dungeonRepository'] === null){
            throw new ParameterNotFoundException('Paramètre dungeonRepository nécessaire au formulaire');
        }

        if(!array_key_exists('translator', $options) || $options['translator'] === null){
            throw new ParameterNotFoundException('Paramètre translator nécessaire au formulaire');
        }

        $character = $options['character'];
        $dungeonRepository = $options['dungeonRepository'];
        $this->translator = $options['translator'];

        $builder
            ->add('Dungeon', EntityType::class, [
                'required' => true,
                'label' => ' ',
                'class' => Dungeon::class,
                'choices' => $character->getAvailableDungeons($dungeonRepository),
                'choice_label' => function (?Dungeon $dungeon): string {
                    return $this->translator->trans($dungeon->getName(),[],'app');
                }
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('dungeon_create_form_confirm',[],'app')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'character' => null,
            'dungeonRepository' => null,
            'translator' => null
        ]);

        $resolver->setAllowedTypes('character', Character::class);
        $resolver->setAllowedTypes('dungeonRepository', DungeonRepository::class);
    }
}
