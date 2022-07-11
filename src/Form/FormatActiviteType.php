<?php

/*
 * Classe - FormatActiviteType
 *
 * Formulaire d'ajout/édition des éléments commun à tous les Formats d'activité
 * Ce formulaire sera imbriqué dans le format
*/

namespace App\Form;

use App\Entity\Uca\Lieu;
use App\Entity\Uca\Tarif;
use App\Entity\Uca\Utilisateur;
use App\Entity\Uca\NiveauSportif;
use Doctrine\ORM\EntityRepository;
use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\TypeAutorisation;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class FormatActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, [
                'label_format' => 'common.libelle',
            ])
            ->add('description', TextareaType::class, [
                'label_format' => 'common.description',
            ])
            ->add('dateDebutPublication', DateTimeType::class, [
                'label_format' => 'format.date.publication.debut',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y H:i',
                ],
            ])
            ->add('dateFinPublication', DateTimeType::class, [
                'label_format' => 'format.date.publication.fin',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y H:i',
                ],
            ])
            ->add('dateDebutInscription', DateTimeType::class, [
                'label_format' => 'format.date.inscription.debut',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y H:i',
                ],
            ])
            ->add('dateFinInscription', DateTimeType::class, [
                'label_format' => 'format.date.inscription.fin',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y H:i',
                ],
            ])
            ->add('dateDebutEffective', DateTimeType::class, [
                'label_format' => 'format.date.effective.debut',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y H:i',
                    'data-datetimepicker-step' => '15',
                ],
            ])
            ->add('dateFinEffective', DateTimeType::class, [
                'label_format' => 'format.date.effective.fin',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y H:i',
                    'data-datetimepicker-step' => '15',
                ],
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => true,
                'allow_delete' => false,
                'download_uri' => false,
                'image_uri' => false,
                'label_format' => 'format.image.libelle',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control-file'
                ]
            ])
            ->add('promouvoir', ChoiceType::class, [
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'label_format' => 'format.promouvoir',
                'expanded' => true,
                'multiple' => false,
                'required' => true, ])
            ->add(
                $builder->create('profils', FormatActiviteProfilUtilisateurType::class, [
                    'constraints' => new Assert\Valid(),
                    'mapped' => false,
                ])
            )
        ;

        // ComplémentTYpe (code a déplacer)
        $builder
            ->add('niveauxSportifs', EntityType::class, [
                'class' => NiveauSportif::class,
                'choice_label' => 'libelle',
                'label_format' => 'format.niveau.sportif',
                'multiple' => true,
                'expanded' => true,
            ])
        ;
        $autorisationsOptions = [
            'class' => TypeAutorisation::class,
            'choice_label' => 'libelle',
            'label_format' => 'format.autorisations',
            'multiple' => true,
            'required' => false,
            'expanded' => false,
        ];
        if (FormatAchatCarte::class == $options['data_class']) {
            $autorisationsOptions['query_builder'] = function (EntityRepository $er) {
                return $er->createQueryBuilder('ta')
                    ->andWhere('ta.comportement<>4')
                    ->orderBy('ta.libelle', 'ASC')
                ;
            };
        }
        $builder->add('autorisations', EntityType::class, $autorisationsOptions)
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => function (Lieu $lieu) {
                    if (null != $lieu->getEtablissement()) {
                        return $lieu->getEtablissement()->getLibelle().' - '.$lieu->getLibelle();
                    }

                    return $lieu->getLibelle();
                },
                'label_format' => 'format.lieu',
                'multiple' => true,
                'expanded' => false,
                'required' => true,
                'attr' => ['placeholder' => 'format.lieu.placeholder'],
            ])
            ->add('estEncadre', ChoiceType::class, [
                'label_format' => 'format.encadre',
                'choices' => [
                    'common.oui' => true,
                    'common.non' => false,
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true,
            ])

            ->add('encadrants', EntityType::class, [
                'class' => Utilisateur::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->innerJoin('u.groups', 'g')
                        ->andWhere('g.roles like \'%ROLE_ENCADRANT%\'')
                        ->orderBy('u.nom', 'ASC')
                    ;
                },
                'choice_label' => function ($encadrant) {
                    return ucfirst($encadrant->getNom().' '.ucfirst($encadrant->getPrenom()));
                },
                'label_format' => 'format.encadrants',
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'encadreToShow nonEncadreToHide',
                ],
            ])
            ->add('estPayant', ChoiceType::class, [
                'label_format' => 'format.payant',
                'choices' => [
                    'common.oui' => true,
                    'common.non' => false,
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true,
            ])
            ->add('tarif', EntityType::class, [
                'class' => Tarif::class,
                'choice_label' => 'libelle',
                'label_format' => 'format.tarif',
                'multiple' => false,
                'expanded' => false,
                'placeholder' => 'format.tarif.placeholder',
                'attr' => [
                    'class' => 'payantToShow nonPayantToHide',
                ],
            ])
            ->add('capacite', TextType::class, [
                'label_format' => 'common.capacite.totale',
            ])
            ->add('statut', ChoiceType::class, [
                'label_format' => 'format.statut',
                'choices' => [
                    'format.statut.brouillon' => 0,
                    'format.statut.publie' => 1,
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => true,
            ])
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (!$view->vars['valid']) {
            ksort($view['profils']['capaciteProfil']->vars['value']);
            ksort($view['profils']['capaciteProfil']->vars['data']);
            ksort($view['profils']['capaciteProfil']->children);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Uca\FormatSimple',
            'inherit_data' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_format_activite';
    }
}
