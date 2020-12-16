<?php

/*
 * Classe - StatistiqueType
 *
 * Formulaire de selection des statistiques personnalisées
*/

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatistiqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        extract($options);

        $builder
            ->add('type_activite', ChoiceType::class, [
                'label_format' => 'common.type.activite',
                'choices' => $typeActivite['choicesList'],
                'attr' => ['class' => 'champRechercheDatatableInscription'],
            ])
            ->add('classe_activite', ChoiceType::class, [
                'label_format' => 'common.classe.activite',
                'choices' => $classeActivite['choicesList'],
                'choice_attr' => function ($val, $key) use ($classeActivite) {
                    return 0 != $val ? ['data-classe_activite-id' => $val, 'data-type_activite-id' => $classeActivite['typeActivite'][$val]]
                    : ['data-classe_activite-id' => 0, 'data-type_activite-id' => 0];
                },
                'attr' => ['class' => 'champRechercheDatatableInscription'],
            ])
            ->add('activite', ChoiceType::class, [
                'label_format' => 'activite.libelle',
                'choices' => $listeActivite['choicesList'],
                'choice_attr' => function ($val, $key) use ($listeActivite) {
                    return 0 != $val ? ['data-activite-id' => $val, 'data-classe_activite-id' => $listeActivite['classeActivite'][$val], 'data-type_activite-id' => $listeActivite['typeActivite'][$val]]
                    : ['data-activite-id' => 0, 'data-classe_activite-id' => 0, 'data-type_activite-id' => 0];
                },
                'attr' => ['class' => 'champRechercheDatatableInscription'],
            ])
            ->add('formatActivite', ChoiceType::class, [
                'label_format' => 'formatactivite.libelle',
                'choices' => $listeFormatActivite['choicesList'],
                'choice_attr' => function ($val, $key) use ($listeFormatActivite) {
                    return 0 != $val ? ['data-activite-id' => $listeFormatActivite['activite'][$val], 'data-format_activite-id' => $val, 'data-creneau' => $listeFormatActivite['hasCreneau'][$val]]
                    : ['data-activite-id' => 0, 'data-format_activite-id' => 0];
                },
                'attr' => ['class' => 'hidden champRechercheDatatableInscription'],
            ])
            ->add('creneau', ChoiceType::class, [
                'label_format' => 'common.creneaux',
                'choices' => $listeCreneau['choicesList'],
                'choice_attr' => function ($val, $key) use ($listeCreneau) {
                    if (0 === strpos($val, 'allCreneaux')) {
                        return ['data-format_activite-id' => str_replace('allCreneaux_', '', $val), 'data-type' => 'format'];
                    }

                    return 0 != $val ? ['data-format_activite-id' => $listeCreneau['formatActivite'][$val], 'data-type' => 'creneau']
                    : ['data-format_activite-id' => 0, 'data-type' => 'creneau'];
                },
                'attr' => ['class' => 'hidden champRechercheDatatableInscription'],
            ])
            ->add('options', ChoiceType::class, [
                'label_format' => 'Options',
                'choices' => [
                    'statistique.options.flux' => 0,
                    'statistique.options.nbetudperso' => 1,
                    'statistique.options.frequentation' => 2,
                    'statistique.options.recurrence' => 3,
                    'statistique.options.nbachatcarte' => 4,
                ],
                'attr' => ['class' => 'champDetailInscription'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'em' => null,
            'typeActivite' => null,
            'classeActivite' => null,
            'listeActivite' => null,
            'listeFormatActivite' => null,
            'listeCreneau' => null,
            'listeEncadrant' => null,
            'listeEtablissement' => null,
            'listeLieu' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_statistique';
    }
}
