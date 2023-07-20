<?php

namespace App\Form;

use App\Entity\Raid;
use App\Repository\RaidRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateRaidInstanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if(!array_key_exists('character', $options) || $options['character'] === null){
            throw new ParameterNotFoundException('Paramètre character nécessaire au formulaire');
        }

        if(!array_key_exists('raidRepository', $options) || $options['raidRepository'] === null){
            throw new ParameterNotFoundException('Paramètre raidRepository nécessaire au formulaire');
        }

        $character = $options['character'];
        $dungeonRepository = $options['raidRepository'];

        $builder
            ->add('Raid', EntityType::class, [
                'required' => true,
                'label' => ' ',
                'class' => Raid::class,
                'choices' => $character->getAvailableRaids($dungeonRepository),
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
            'raidRepository' => null
        ]);

        $resolver->setAllowedTypes('raidRepository', RaidRepository::class);
    }
}
