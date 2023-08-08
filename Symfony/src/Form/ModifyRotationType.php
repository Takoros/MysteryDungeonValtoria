<?php

namespace App\Form;

use App\Entity\Attack;
use App\Entity\Rotation;
use App\Service\CharacterService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModifyRotationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if(!array_key_exists('character', $options) || $options['character'] === null){
            throw new ParameterNotFoundException('Paramètre character nécessaire au formulaire');
        }

        if(!array_key_exists('characterService', $options) || $options['characterService'] === null){
            throw new ParameterNotFoundException('Paramètre characterService nécessaire au formulaire');
        }

        $character = $options['character'];
        $characterService = $options['characterService'];

        $builder
            ->add('attackOne', EntityType::class, [
                'class' => Attack::class,
                'choices' => $characterService->getAvailableAttacks($character),
                'choice_label' => function (?Attack $attack): string {
                    return $attack->getName() .' ('.$attack->getType()->getName().') ';
                },
            ])
            ->add('attackTwo', EntityType::class, [
                'class' => Attack::class,
                'choices' => $characterService->getAvailableAttacks($character),
                'choice_label' => function (?Attack $attack): string {
                    return $attack->getName() .' ('.$attack->getType()->getName().') ';
                },
            ])
            ->add('attackThree', EntityType::class, [
                'class' => Attack::class,
                'choices' => $characterService->getAvailableAttacks($character),
                'choice_label' => function (?Attack $attack): string {
                    return $attack->getName() .' ('.$attack->getType()->getName().') ';
                },
            ])
            ->add('attackFour', EntityType::class, [
                'class' => Attack::class,
                'choices' => $characterService->getAvailableAttacks($character),
                'choice_label' => function (?Attack $attack): string {
                    return $attack->getName() .' ('.$attack->getType()->getName().') ';
                },
            ])
            ->add('attackFive', EntityType::class, [
                'class' => Attack::class,
                'choices' => $characterService->getAvailableAttacks($character),
                'choice_label' => function (?Attack $attack): string {
                    return $attack->getName() .' ('.$attack->getType()->getName().') ';
                },
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Confirmer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rotation::class,
            'character' => null,
            'characterService' => null
        ]);
    }
}
