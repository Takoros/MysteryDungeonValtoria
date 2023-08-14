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
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateCharacterType extends AbstractType
{
    public TranslatorInterface $translator;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if(!array_key_exists('speciesRepository', $options) || $options['speciesRepository'] === null){
            throw new ParameterNotFoundException('Paramètre speciesRepository nécessaire au formulaire');
        }

        if(!array_key_exists('translator', $options) || $options['translator'] === null){
            throw new ParameterNotFoundException('Paramètre translator nécessaire au formulaire');
        }
        
        $speciesRepository = $options['speciesRepository'];
        $this->translator = $options['translator'];

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
                    $this->translator->trans('genre_male', [], 'app') => 'Mâle',
                    $this->translator->trans('genre_female', [], 'app') => 'Femelle',
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
                'choice_value' => 'name',
                'choice_label' => function (?Species $species): string {
                    return $this->translator->trans( $species->getId().'_species_name' ,[],'app');
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
            'data_class' => Character::class,
            'speciesRepository' => null,
            'translator' => null
        ]);
    }
}
