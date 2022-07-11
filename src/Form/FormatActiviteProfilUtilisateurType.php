<?php

/*
 * Classe - FormatActiviteProfilUtilisateurType
 *
 * Formulaire d'ajout/édition les profils autorisié pour une activité ainsi que le nombre de places
 * Ce formulaire est imbriqué dans le format d'activité
*/

namespace App\Form;

use Doctrine\ORM\EntityManager;
use App\Entity\Uca\ProfilUtilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\DataMappers\FormatActiviteProfilUtilisateurDataMapper;

class FormatActiviteProfilUtilisateurType extends AbstractType
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profilUtilisateur', EntityType::class, [
                'class' => ProfilUtilisateur::class,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->andWhere('p.parent is null')
                        ->orderBy('p.libelle', 'asc')
                    ;
                },
                'choice_label' => 'libelle',
                'label_format' => 'format.profils.utilisateur',
                'multiple' => true,
                'expanded' => true,
                'choice_attr' => function () {
                    return ['class' => 'choixProfils'];
                },
                'constraints' => new Assert\NotBlank([
                    'message' => 'complement.profilsutilisateurs.notblank',
                    'groups' => $options['validation_group'],
                ]),
            ])
            ->add(
                $builder->create('capaciteProfil', CollectionType::class, [
                    'entry_type' => NumberType::class,
                    'label_format' => 'format.profils.utilisateur.capacite',
                    'entry_options' => ['label' => false],
                    'mapped' => false,
                    'by_reference' => false,
                    'required' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'prototype' => true,
                    //'error_bubbling' => true,
                    'constraints' => new Assert\NotBlank([
                        'message' => 'formatactivite.capaciteprofil.notBlank',
                        'groups' => $options['validation_group'],
                    ]),
                ])
                    ->addViewTransformer(new CallbackTransformer(
                        function ($dataToArray) {return $dataToArray; },
                        function ($arrayToData) {return  $arrayToData; }
                    ))
            )
        ;

        $builder->setDataMapper(new FormatActiviteProfilUtilisateurDataMapper($this->em));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null, //'App\Entity\Uca\FormatActiviteProfilUtilisateur',
            'empty_data' => null,
            'validation_group' => 'default',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_format_activite_profil';
    }
}
