<?php

/*
 * Classe - RechercheActiviteType
 *
 * Formulaire de recherche d'activité (UcaWeb)
*/

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use UcaBundle\Entity\Activite;
use UcaBundle\Entity\ClasseActivite;
use UcaBundle\Entity\Etablissement;
use UcaBundle\Entity\Lieu;
use UcaBundle\Entity\TypeActivite;

class RechercheActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $em = $options['data']['em'];

        $listeTypeActivite = ['traduction.tous' => 0];
        foreach ($em->getRepository(TypeActivite::class)->findAll() as $typeActivite) {
            $listeTypeActivite[$typeActivite->getLibelle()] = $typeActivite->getId();
        }
        $builder->add('type_activite', ChoiceType::class, [
            'label_format' => 'common.type.activite',
            'choices' => $listeTypeActivite,
            'attr' => ['class' => 'champRechercheActivite'],
            'required' => false,
        ]);

        $listeClasseActivite = ['traduction.tous' => 0];
        foreach ($em->getRepository(ClasseActivite::class)->findAll() as $classeActivite) {
            $listeClasseActivite[$classeActivite->getLibelle()] = $classeActivite->getId();
        }
        $builder->add('classe_activite', ChoiceType::class, [
            'label_format' => 'common.classe.activite',
            'choices' => $listeClasseActivite,
            'choice_attr' => function ($val, $key) use ($em) {
                return 0 != $val ? ['data-classe_activite-id' => $val, 'data-type_activite-id' => $em->getRepository(ClasseActivite::class)->findOneById($val)->getTypeActivite()->getId()]
                : ['data-classe_activite-id' => 0, 'data-type_activite-id' => 0];
            },
            'attr' => ['class' => 'champRechercheActivite'],
            'required' => false,
        ]);

        $choixActivite = ['traduction.tous' => 0];
        foreach ($em->getRepository(Activite::class)->findAll() as $activite) {
            $choixActivite[$activite->getLibelle()] = $activite->getId();
        }
        $builder->add('activite', ChoiceType::class, [
            'label_format' => 'activite.libelle',
            'choices' => $choixActivite,
            'choice_attr' => function ($val, $key) use ($em) {
                return 0 != $val ? ['data-activite-id' => $val, 'data-classe_activite-id' => $em->getRepository(Activite::class)->findOneById($val)->getClasseActivite()->getId(),
                    'data-type_activite-id' => $em->getRepository(Activite::class)->findOneById($val)->getClasseActivite()->getTypeActivite()->getId(), ]
                : ['data-activite-id' => 0, 'data-classe_activite-id' => 0, 'data-type_activite-id' => 0];
            },
            'attr' => ['class' => 'champRechercheDatatableInscription'],
            'required' => false,
        ]);

        $builder->add('format_activite', ChoiceType::class, [
            'label_format' => 'formatactivite.libelle',
            'choices' => [
                'traduction.tous' => '0',
                'formataveccreneau.title' => 'FormatAvecCreneau',
                'formatavecreservation.title' => 'FormatAvecReservation',
                'formatsimple.title' => 'FormatSimple',
                'formatachatcarte.title' => 'FormatAchatCarte',
            ],
            'attr' => ['class' => 'champRechercheActivite'],
            'required' => false,
        ]);

        $listeCampus = ['traduction.tous' => 0];
        foreach ($em->getRepository(Etablissement::class)->findAll() as $etablissement) {
            $listeCampus[$etablissement->getLibelle()] = $etablissement->getId();
        }
        $builder->add('etablissement', ChoiceType::class, [
            'label_format' => 'etablissement.libelle',
            'choices' => $listeCampus,
            'attr' => ['class' => 'champRechercheActivite'],
            'required' => false,
        ]);

        $listeLieu = ['traduction.tous' => 0];
        foreach ($em->getRepository(Lieu::class)->findAll() as $lieu) {
            $listeLieu[$lieu->getLibelle()] = $lieu->getId();
        }
        $builder->add('lieu', ChoiceType::class, [
            'label_format' => 'common.lieu',
            'choices' => $listeLieu,
            'choice_attr' => function ($val, $key) use ($em) {
                if (0 != $val) {
                    if (null == $em->getRepository(Lieu::class)->findOneById($val)->getEtablissement()) {
                        return ['data-lieu-id' => $val, 'data-etablissement-id' => 0];
                    }

                    return ['data-lieu-id' => $val, 'data-etablissement-id' => $em->getRepository(Lieu::class)->findOneById($val)->getEtablissement()->getId()];
                }

                return ['data-lieu-id' => 0, 'data-etablissement-id' => 0];
            },
            'attr' => ['class' => 'champRechercheActivite'],
            'required' => false,
        ]);

        $builder->add('dateDebut', DateTimeType::class, [
            'label_format' => 'common.date.debut',
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy HH:mm',
            'attr' => [
                'class' => 'datetimepicker',
                'data-datetimepicker-format' => 'd/m/Y H:i',
            ],
            'required' => false,
        ])
            ->add('dateFin', DateTimeType::class, [
                'label_format' => 'common.date.fin',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y H:i',
                ],
                'required' => false,
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_rechercheactivite';
    }
}
