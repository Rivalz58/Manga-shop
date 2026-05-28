<?php

namespace App\Form;

use App\Document\Avis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('auteurNom', TextType::class, ['label' => 'Votre nom'])
            ->add('auteurEmail', EmailType::class, ['label' => 'Votre email'])
            ->add('note', ChoiceType::class, [
                'label' => 'Note',
                'choices' => ['⭐' => 1, '⭐⭐' => 2, '⭐⭐⭐' => 3, '⭐⭐⭐⭐' => 4, '⭐⭐⭐⭐⭐' => 5],
                'expanded' => false,
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire',
                'attr' => ['rows' => 4, 'placeholder' => 'Partagez votre avis sur cette carte...'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Avis::class]);
    }
}
