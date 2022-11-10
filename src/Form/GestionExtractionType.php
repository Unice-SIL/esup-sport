<?php

/*
 * Classe - GestionExtractionType
 *
 * Formulaire permetant dze gérér les élements qui seront extraits
*/

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GestionExtractionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('activite', CheckboxType::class, [
            'label_format' => 'activite.libelle',
            'required' => false,
            'attr' => ['class' => 'champRechercheExtraction'],
        ]);

        $builder->add('classe_activite', CheckboxType::class, [
            'label_format' => 'common.classe.activite',
            'required' => false,
            'attr' => ['class' => 'champRechercheExtraction'],
        ]);

        $builder->add('creneau', CheckboxType::class, [
            'label_format' => 'common.creneaux',
            'required' => false,
            'attr' => ['class' => 'champRechercheExtraction'],
        ]);

        $builder->add('encadrant', CheckboxType::class, [
            'label_format' => 'common.encadrants',
            'required' => false,
            'attr' => ['class' => 'champRechercheExtraction'],
        ]);

        $builder->add('formatActivite', CheckboxType::class, [
            'label_format' => 'formatactivite.libelle',
            'required' => false,
            'attr' => ['class' => 'champRechercheExtraction'],
        ]);

        $builder->add('type_activite', CheckboxType::class, [
            'label_format' => 'common.type.activite',
            'required' => false,
            'attr' => ['class' => 'champRechercheExtraction'],
        ]);

        $builder->add('inscription', CheckboxType::class, [
            'label_format' => 'utilisateur.inscription.libelle',
            'required' => false,
            'attr' => ['class' => 'champRechercheExtraction'],
        ]);

        $builder->add('statut', ChoiceType::class, [
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
            'attr' => ['class' => 'champRechercheExtraction hidden hidden_inscription'],
        ]);

        $builder->add('creneauDetails', ChoiceType::class, [
            'label_format' => 'common.creneaux.details',
            'multiple' => true,
            'choices' => [
                'etablissement.libelle' => 8,
                'common.lieu' => 9,
                'common.description' => 1,
                'common.tarif' => 2,
                'common.capacite' => 3,
                'format.profils.utilisateur' => 4,
                'format.niveau.sportif' => 5,
                'common.eligible' => 6,
                'common.periode' => 7,
            ],
            'attr' => ['class' => 'champRechercheExtraction hidden hidden_creneau'],
        ]);

        $builder->add('formatActiviteDetails', ChoiceType::class, [
            'label_format' => 'common.format.activite.details',
            'multiple' => true,
            'choices' => [
                'common.description' => 1,
                'common.date.effectives' => 2,
                'common.date.inscriptions' => 3,
                'common.date.publication' => 4,
                'common.capacite' => 5,
                'common.statut' => 6,
                'common.payant' => 7,
                'common.tarif' => 8,
                'format.niveau.sportif' => 9,
                'format.profils.utilisateur' => 10,
                'format.autorisations' => 11,
                'common.ressources' => 12,
                'common.carte.acheter' => 13,
            ],
            'attr' => ['class' => 'champRechercheExtraction hidden hidden_formatActivite'],
        ]);

        $builder->add('inscriptionDetails', ChoiceType::class, [
            'label_format' => 'common.inscription.details',
            'multiple' => true,
            'choices' => [
                'common.nom.prenom' => 1,
                'common.dateinscription' => 2,
                'common.datevalidation' => 3,
                'common.datedesinscription' => 4,
                'detail.inscription.motifannulation' => 5,
                'detail.inscription.commentaireannulation' => 6,
            ],
            'attr' => ['class' => 'champRechercheExtraction hidden hidden_inscription'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_extraction';
    }
}
