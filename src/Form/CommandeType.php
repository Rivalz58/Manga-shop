<?php

namespace App\Form;

use App\Document\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('clientNom', TextType::class, ['label' => 'Nom complet'])
            ->add('clientEmail', EmailType::class, ['label' => 'Email'])
            ->add('adresseLivraison', TextareaType::class, [
                'label' => 'Adresse de livraison',
                'attr' => ['rows' => 3, 'placeholder' => 'Numéro, rue, code postal, ville'],
            ])
            ->add('noteLivraison', TextareaType::class, [
                'label' => 'Note pour la livraison (optionnel)',
                'required' => false,
                'attr' => ['rows' => 2],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Commande::class]);
    }
}
