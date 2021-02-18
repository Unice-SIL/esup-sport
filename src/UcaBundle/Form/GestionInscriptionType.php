<?php

/*
 * Classe - GestionInscriptionType
 *
 * Formulaire de recherche du reporting des inscriptions
*/

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GestionInscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        extract($options);

        $builder
            ->add('nom', TextType::class, [
                'label_format' => 'utilisateur.nom',
                'attr' => ['class' => 'champRechercheDatatableInscription'],
                'required' => false,
            ])
            ->add('prenom', TextType::class, [
                'label_format' => 'utilisateur.prenom',
                'attr' => ['class' => 'champRechercheDatatableInscription'],
                'required' => false,
            ])
            ->add('statut', ChoiceType::class, [
                'label_format' => 'common.statut',
                'choices' => [
                    'traduction.tous' => '0',
                    'common.annule' => 'annule',
                    'common.valide' => 'valide',
                    'common.attentepaiement' => 'attentepaiement',
                    'common.attentevalidationencadrant' => 'attentevalidationencadrant',
                    'common.attentevalidationgestionnaire' => 'attentevalidationgestionnaire',
                    'common.ancienneinscription' => 'ancienneinscription',
                    'common.desinscriptionadministrative' => 'desinscriptionadministrative',
                ],
                'attr' => ['class' => 'champRechercheDatatableInscription'],
                'required' => false,
                'data' => 0,
            ])
            ->add('type_activite', ChoiceType::class, [
                'label_format' => 'common.type.activite',
                'choices' => $typeActivite['choicesList'],
                'attr' => ['class' => 'champRechercheDatatableInscription'],
                'required' => false,
                'data' => 0,
            ])
            ->add('classe_activite', ChoiceType::class, [
                'label_format' => 'common.classe.activite',
                'choices' => $classeActivite['choicesList'],
                'choice_attr' => function ($val, $key) use ($classeActivite) {
                    return 0 != $val ? ['data-classe_activite-id' => $val, 'data-type_activite-id' => $classeActivite['typeActivite'][$val]]
                    : ['data-classe_activite-id' => 0, 'data-type_activite-id' => 0];
                },
                'attr' => ['class' => 'champRechercheDatatableInscription'],
                'required' => false,
                'data' => 0,
            ])
            ->add('activite', ChoiceType::class, [
                'label_format' => 'activite.libelle',
                'choices' => $listeActivite['choicesList'],
                'choice_attr' => function ($val, $key) use ($listeActivite) {
                    return 0 != $val ? ['data-activite-id' => $val, 'data-classe_activite-id' => $listeActivite['classeActivite'][$val], 'data-type_activite-id' => $listeActivite['typeActivite'][$val]]
                    : ['data-activite-id' => 0, 'data-classe_activite-id' => 0, 'data-type_activite-id' => 0];
                },
                'attr' => ['class' => 'champRechercheDatatableInscription'],
                'required' => false,
                'data' => 0,
            ])
            ->add('formatActivite', ChoiceType::class, [
                'label_format' => 'formatactivite.libelle',
                'choices' => $listeFormatActivite['choicesList'],
                'choice_attr' => function ($val, $key) use ($listeFormatActivite) {
                    return 0 != $val ? ['data-activite-id' => $listeFormatActivite['activite'][$val], 'data-format_activite-id' => $val, 'data-creneau' => $listeFormatActivite['hasCreneau'][$val]]
                    : ['data-activite-id' => 0, 'data-format_activite-id' => 0];
                },
                'attr' => ['class' => 'hidden champRechercheDatatableInscription'],
                'required' => false,
                'data' => 0,
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
                'required' => false,
                'data' => 0,
            ])
            ->add('encadrants', ChoiceType::class, [
                'label_format' => 'common.encadrant',
                'choices' => $listeEncadrant['choicesList'],
                'attr' => ['class' => 'champRechercheDatatableInscription'],
                'required' => false,
                'data' => 0,
            ])
            ->add('etablissements', ChoiceType::class, [
                'label_format' => 'etablissement.libelle',
                'choices' => $listeEtablissement['choicesList'],
                'attr' => ['class' => 'champRechercheDatatableInscription'],
                'required' => false,
                'data' => 0,
            ])
            ->add('lieux', ChoiceType::class, [
                'label_format' => 'common.lieu',
                'choices' => $listeLieu['choicesList'],
                'choice_attr' => function ($val, $key) use ($listeLieu) {
                    return 0 != $val ? ['data-lieux-id' => $val, 'data-etablissements-id' => $listeLieu['etablissement'][$val]]
                    : ['data-lieux-id' => 0, 'data-etablissements-id' => 0];
                },
                'attr' => ['class' => 'champRechercheDatatableInscription'],
                'required' => false,
                'data' => 0,
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
        return 'ucabundle_inscription';
    }
}
