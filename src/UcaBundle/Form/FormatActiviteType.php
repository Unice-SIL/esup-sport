<?php

namespace UcaBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;
use UcaBundle\Entity\FormatAchatCarte;

class FormatActiviteType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, [
                'label_format' => 'common.libelle'
            ])
            ->add('description', TextareaType::class, [
                'label_format' => 'common.description'
            ])
            ->add('dateDebutPublication', DateTimeType::class, [
                'label_format' => 'format.date.publication.debut',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy  H:m'
            ])
            ->add('dateFinPublication', DateTimeType::class, [
                'label_format' => 'format.date.publication.fin',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy  H:m'
            ])
            ->add('dateDebutInscription', DateTimeType::class, [
                'label_format' => 'format.date.inscription.debut',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy  H:m'
            ])
            ->add('dateFinInscription', DateTimeType::class, [
                'label_format' => 'format.date.inscription.fin',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy  H:m'
            ])
            ->add('dateDebutEffective', DateTimeType::class, [
                'label_format' => 'format.date.effective.debut',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy  H:m'
            ])
            ->add('dateFinEffective', DateTimeType::class, [
                'label_format' => 'format.date.effective.fin',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy  H:m'
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => true,
                'allow_delete' => false,
                'download_uri' => false,
                'image_uri' => false,
                'label_format' => 'format.image.libelle',
                'translation_domain' => 'messages',
            ]);

        // ComplémentTYpe (code a déplacer)
        $builder
            ->add('niveauxSportifs', EntityType::class, [
                'class' => 'UcaBundle:NiveauSportif',
                'choice_label' => 'libelle',
                'label_format' => 'format.niveau.sportif',
                'multiple' => true,
                'expanded' => true
            ])
            ->add('profilsUtilisateurs', EntityType::class, [
                'class' => 'UcaBundle:ProfilUtilisateur',
                'choice_label' => 'libelle',
                'label_format' => 'format.profils.utilisateur',
                'multiple' => true,
                'expanded' => true
            ]);
        $autorisationsOptions = [
            'class' => 'UcaBundle:TypeAutorisation',
            'choice_label' => 'libelle',
            'label_format' => 'format.autorisations',
            'multiple' => true,
            'required' => false,
            'expanded' => false
        ];
        if ($options['data_class'] == FormatAchatCarte::class) {
            $autorisationsOptions['query_builder'] = function (EntityRepository $er) {
                return $er->createQueryBuilder('ta')
                    ->andWhere('ta.comportement<>4')
                    ->orderBy('ta.libelle', 'ASC');
            };
        }
        $builder->add('autorisations', EntityType::class, $autorisationsOptions)
            ->add('lieu', EntityType::class, [
                'class' => 'UcaBundle:Lieu',
                'choice_label' => 'libelle',
                'label_format' => 'format.lieu',
                'multiple' => true,
                'expanded' => false,
                'required' => true,
                'attr' => ['placeholder' => 'format.lieu.placeholder']
            ])
            ->add('estEncadre', ChoiceType::class, [
                'label_format' => 'format.encadre',
                'choices'  => [
                    'common.oui' => true,
                    'common.non' => false,
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true
            ])

            ->add('encadrants', EntityType::class, [
                'class' => 'UcaBundle:Utilisateur',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->innerJoin('u.groups', 'g')
                        ->andWhere('g.id=3')
                        ->orderBy('u.nom', 'ASC');
                },
                'choice_label' => function ($encadrant) {
                    return ucfirst($encadrant->getNom() . " " . ucfirst($encadrant->getPrenom()));
                },
                'label_format' => 'format.encadrants',
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'encadreToShow nonEncadreToHide'
                ]
            ])
            ->add('estPayant', ChoiceType::class, [
                'label_format' => 'format.payant',
                'choices'  => [
                    'common.oui' => true,
                    'common.non' => false,
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true
            ])
            ->add('tarif', EntityType::class, [
                'class' => 'UcaBundle:Tarif',
                'choice_label' => 'libelle',
                'label_format' => 'format.tarif',
                'multiple' => false,
                'expanded' => false,
                'placeholder' => 'format.tarif.placeholder',
                'attr' => [
                    'class' => 'payantToShow nonPayantToHide'
                ]
            ])
            ->add('capacite', TextType::class, [
                'label_format' => 'common.capacite'
            ])
            ->add('statut', ChoiceType::class, [
                'label_format' => 'format.statut',
                'choices'  => [
                    'format.statut.brouillon' => 0,
                    'format.statut.publie' => 1,
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\FormatSimple',
            'inherit_data' => true

        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_format_activite';
    }
}
