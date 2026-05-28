<?php

namespace App\Form;

use App\Document\Carte;
use App\Document\Serie;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;

class CarteType extends AbstractType
{
    public function __construct(private DocumentManager $dm) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $series = $this->dm->getRepository(Serie::class)->findAllOrderedByAnnee();
        $seriesChoices = [];
        foreach ($series as $serie) {
            $seriesChoices[$serie->getNom() . ' (' . $serie->getAnnee() . ')'] = $serie->getId();
        }

        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la carte',
                'attr' => ['placeholder' => 'Ex: Pikachu'],
            ])
            ->add('serieId', ChoiceType::class, [
                'label' => 'Série',
                'choices' => $seriesChoices,
                'mapped' => false,
                'placeholder' => '-- Choisir une série --',
            ])
            ->add('typePokemon', ChoiceType::class, [
                'label' => 'Type',
                'choices' => array_combine(Carte::TYPES, Carte::TYPES),
            ])
            ->add('rarete', ChoiceType::class, [
                'label' => 'Rareté',
                'choices' => array_combine(Carte::RARETES, Carte::RARETES),
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (€)',
                'scale' => 2,
                'attr' => ['step' => '0.01', 'min' => '0.01'],
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'attr' => ['min' => '0'],
            ])
            ->add('pv', IntegerType::class, [
                'label' => 'PV (Points de Vie)',
                'required' => false,
                'attr' => ['min' => '0'],
            ])
            ->add('numeroPokedex', IntegerType::class, [
                'label' => 'Numéro Pokédex',
                'required' => false,
                'attr' => ['min' => '0'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 4],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image de la carte',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Format accepté : JPG, PNG, WebP',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Carte::class,
        ]);
    }
}
