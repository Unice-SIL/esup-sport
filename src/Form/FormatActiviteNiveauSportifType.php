<?php

/*
 * Classe - FormatActiviteNiveauSportifType
 *
 * Formulaire d'ajout/édition les niveaux sportif autorisé pour une activité ainsi que le nombre de places
 * Ce formulaire est imbriqué dans le format d'activité
*/

namespace App\Form;

use App\Entity\Uca\NiveauSportif;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\DataMappers\FormatActiviteNiveauSportifDataMapper;

class FormatActiviteNiveauSportifType extends AbstractType
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('niveauSportif', EntityType::class, [
                'class' => NiveauSportif::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.libelle', 'asc')
                    ;
                },
                'choice_label' => 'libelle',
                'label_format' => 'format.niveau.sportif',
                'multiple' => true,
                'expanded' => true,
                'choice_attr' => function () {
                    return ['class' => 'choixNiveaux'];
                },
                'constraints' => new Assert\NotBlank([
                    'message' => 'complement.niveauxsportifs.notblank',
                    'groups' => $options['validation_group'],
                ]),
            ])
            ->add(
                $builder->create('detail', CollectionType::class, [
                    'entry_type' => TextareaType::class,
                    'label_format' => 'format.niveau.sportif.detail',
                    'entry_options' => [
                        'label' => false,
                        'attr' => [
                            'rows' => 3,
                        ],
                    ],
                    'mapped' => false,
                    'by_reference' => false,
                    'required' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    //'error_bubbling' => true,
                    'constraints' => new Assert\NotBlank([
                        'message' => 'formatactivite.niveausportif.detail.notBlank',
                        'groups' => $options['validation_group'],
                    ]),
                ])
                    ->addViewTransformer(new CallbackTransformer(
                        function ($dataToArray) {
                            return $dataToArray;
                        },
                        function ($arrayToData) {
                            return  $arrayToData;
                        }
                    ))
            )
        ;

        $builder->setDataMapper(new FormatActiviteNiveauSportifDataMapper($this->em));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null, //'App\Entity\Uca\FormatActiviteNiveauSportif',
            'empty_data' => null,
            'validation_group' => 'default',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_format_activite_niveau';
    }
}
