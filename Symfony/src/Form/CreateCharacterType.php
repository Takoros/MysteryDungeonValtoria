<?php

namespace App\Form;

use App\Entity\Character;
use App\Entity\Species;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateCharacterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if(!array_key_exists('speciesRepository', $options) || $options['speciesRepository'] === null){
            throw new ParameterNotFoundException('Paramètre speciesRepository nécessaire au formulaire');
        }
        
        $speciesRepository = $options['speciesRepository'];

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Rick, Bob, Krom..',
                    'max' => 30,
                ]
            ])
            ->add('gender', ChoiceType::class, [
                'choices'  => [
                    'Mâle' => 'Mâle',
                    'Femelle' => 'Femelle',
                ],
            ])
            ->add('age', IntegerType::class, [
                'required' => true,
                'attr' => [
                    'min' => 18,
                    'max' => 44,
                    'value' => 18
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'attr' => [
                    'maxLength' => 200
                ]
            ])
            ->add('Species', EntityType::class, [
                'class' => Species::class,
                'choices' => $speciesRepository->findBy(['isPlayable' => true]),
                'choice_label' => 'name',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Confirmer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Character::class,
            'speciesRepository' => null
        ]);
    }
}
