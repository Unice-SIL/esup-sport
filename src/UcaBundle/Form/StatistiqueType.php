<?php

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UcaBundle\Entity\Activite;
use UcaBundle\Entity\ClasseActivite;
use UcaBundle\Entity\Creneau;
use UcaBundle\Entity\DhtmlxEvenement;
use UcaBundle\Entity\DhtmlxSerie;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Entity\FormatAvecCreneau;
use UcaBundle\Entity\TypeActivite;

class StatistiqueType extends AbstractType
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
            'attr' => ['class' => 'champDetailInscription'],
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
            'attr' => ['class' => 'champDetailInscription'],
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
            'attr' => ['class' => 'champDetailInscription'],
        ]);

        $choixFormatActivite = ['traduction.tous' => 0];

        $listeCreneau = ['traduction.tous' => 0];

        foreach ($em->getRepository(FormatActivite::class)->findAll() as $formatActivite) {
            if ($formatActivite instanceof FormatAvecCreneau) {
                $listeCreneau[$formatActivite->getLibelle()] = 'allCreneaux_'.$formatActivite->getId();
            }
            $choixFormatActivite[$formatActivite->getLibelle()] = $formatActivite->getId();
        }

        $builder->add(
            'formatActivite',
            ChoiceType::class,
            [
                'label_format' => 'formatactivite.libelle',
                'choices' => $choixFormatActivite,
                'choice_attr' => function ($val, $key) use ($em) {
                    return 0 != $val ? ['data-activite-id' => $em->getRepository(FormatActivite::class)->findOneById($val)->getActivite()->getId(),
                        'data-format_activite-id' => $em->getRepository(FormatActivite::class)->findOneById($val)->getId(),
                        'data-creneau' => $em->getRepository(FormatActivite::class)->findOneById($val) instanceof FormatAvecCreneau ? 'true' : 'false', ]
                        : ['data-activite-id' => 0, 'data-format_activite-id' => 0];
                },
                'attr' => ['class' => 'champDetailInscription hidden'],
            ]
        );

        foreach ($em->getRepository(Creneau::class)->findAll() as $creneau) {
            if ($em->getRepository(DhtmlxSerie::class)->findOneByCreneau($creneau->getId())) {
                $idSerie = $em->getRepository(DhtmlxSerie::class)->findOneByCreneau($creneau->getId())->getID();

                if ($em->getRepository(DhtmlxEvenement::class)->findOneBySerie($idSerie)) {
                    $listeCreneau[$creneau->getArticleLibelle()] = $idSerie;
                }
            }
        }

        foreach ($em->getRepository(FormatActivite::class)->findAll() as $format) {
        }

        $builder->add(
            'creneau',
            ChoiceType::class,
            [
                'label_format' => 'common.creneaux',
                'choices' => $listeCreneau,
                'choice_attr' => function ($val, $key) use ($em) {
                    if (0 === strpos($val, 'allCreneaux')) {
                        return ['data-format_activite-id' => str_replace('allCreneaux_', '', $val), 'data-type' => 'format'];
                    }

                    return 0 != $val ? ['data-format_activite-id' => $em->getRepository(DhtmlxSerie::class)->findOneById($val)->getCreneau()->getFormatActivite()->getId(), 'data-type' => 'creneau']
                    : ['data-format_activite-id' => 0, 'data-type' => 'creneau'];
                },
                'attr' => ['class' => 'champDetailInscription hidden'],
            ]
        );

        $builder->add('options', ChoiceType::class, [
            'label_format' => 'Options',
            'choices' => [
                'statistique.options.flux' => 0,
                'statistique.options.nbetudperso' => 1,
                'statistique.options.frequentation' => 2,
                'statistique.options.recurrence' => 3,
                'statistique.options.nbachatcarte' => 4,
            ],
            'attr' => ['class' => 'champDetailInscription'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_statistique';
    }
}
