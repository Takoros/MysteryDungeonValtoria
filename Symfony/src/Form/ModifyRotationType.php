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
use Symfony\Contracts\Translation\TranslatorInterface;

class ModifyRotationType extends AbstractType
{
    public TranslatorInterface $translator;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if(!array_key_exists('character', $options) || $options['character'] === null){
            throw new ParameterNotFoundException('Paramètre character nécessaire au formulaire');
        }

        if(!array_key_exists('characterService', $options) || $options['characterService'] === null){
            throw new ParameterNotFoundException('Paramètre characterService nécessaire au formulaire');
        }

        if(!array_key_exists('translator', $options) || $options['translator'] === null){
            throw new ParameterNotFoundException('Paramètre translator nécessaire au formulaire');
        }

        $character = $options['character'];
        $characterService = $options['characterService'];
        $this->translator = $options['translator'];

        $builder
            ->add('attackOne', EntityType::class, [
                'class' => Attack::class,
                'choices' => $characterService->getAvailableAttacks($character),
                'choice_label' => function (?Attack $attack): string {
                    return $this->translateAttackName($attack->getName()) .' ('.$this->translateTypeName($attack->getType()->getName()).') ';
                },
            ])
            ->add('attackTwo', EntityType::class, [
                'class' => Attack::class,
                'choices' => $characterService->getAvailableAttacks($character),
                'choice_label' => function (?Attack $attack): string {
                    return $this->translateAttackName($attack->getName()) .' ('.$this->translateTypeName($attack->getType()->getName()).') ';
                },
            ])
            ->add('attackThree', EntityType::class, [
                'class' => Attack::class,
                'choices' => $characterService->getAvailableAttacks($character),
                'choice_label' => function (?Attack $attack): string {
                    return $this->translateAttackName($attack->getName()) .' ('.$this->translateTypeName($attack->getType()->getName()).') ';
                },
            ])
            ->add('attackFour', EntityType::class, [
                'class' => Attack::class,
                'choices' => $characterService->getAvailableAttacks($character),
                'choice_label' => function (?Attack $attack): string {
                    return $this->translateAttackName($attack->getName()) .' ('.$this->translateTypeName($attack->getType()->getName()).') ';
                },
            ])
            ->add('attackFive', EntityType::class, [
                'class' => Attack::class,
                'choices' => $characterService->getAvailableAttacks($character),
                'choice_label' => function (?Attack $attack): string {
                    return $this->translateAttackName($attack->getName()) .' ('.$this->translateTypeName($attack->getType()->getName()).') ';
                },
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->translator->trans('dungeon_create_form_confirm', [], 'app')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rotation::class,
            'character' => null,
            'characterService' => null,
            'translator' => null
        ]);
    }
    
    public function translateAttackName($attackName){
        $attackName = strtolower($attackName);
        
        $attackName = str_replace(' ', '_', $attackName);
        $attackName = str_replace('ô', 'o', $attackName);
        $attackName = str_replace('â', 'a', $attackName);
        $attackName = str_replace('à', 'a', $attackName);
        $attackName = str_replace('û', 'u', $attackName);
        $attackName = str_replace('é', 'e', $attackName);
        $attackName = str_replace('É', 'e', $attackName);
        $attackName = str_replace('è', 'e', $attackName);
        $attackName = str_replace('ç', 'c', $attackName);

        return $this->translator->trans($attackName.'_attack', [], 'app');
    }

    public function translateTypeName($typeName){
        $typeName = strtolower($typeName);
        
        $typeName = str_replace(' ', '_', $typeName);
        $typeName = str_replace('ô', 'o', $typeName);
        $typeName = str_replace('â', 'a', $typeName);
        $typeName = str_replace('à', 'a', $typeName);
        $typeName = str_replace('û', 'u', $typeName);
        $typeName = str_replace('é', 'e', $typeName);
        $typeName = str_replace('É', 'e', $typeName);
        $typeName = str_replace('è', 'e', $typeName);
        $typeName = str_replace('ç', 'c', $typeName);

        return $this->translator->trans($typeName.'_type', [], 'app');
    }
}
