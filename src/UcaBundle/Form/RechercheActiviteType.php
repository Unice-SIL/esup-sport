<?php

/*
 * Classe - RechercheActiviteType
 *
 * Formulaire de recherche d'activité (UcaWeb)
*/

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use UcaBundle\Entity\Activite;
use UcaBundle\Entity\Etablissement;

class RechercheActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $em = $options['data']['em'];

        $choixActivite = [
            'activite.recherche.option.typeactivite' => 0,
            'traduction.tous' => 0
        ];
        foreach ($em->getRepository(Activite::class)->findBy([], ['libelle' => 'asc']) as $activite) {
            $choixActivite[$activite->getLibelle()] = $activite->getId();
        }
        $builder->add('activite', ChoiceType::class, [
            'label_format' => 'common.type.activite',
            'choices' => $choixActivite,
            'choice_attr' => function ($val, $key) use ($em) {
                return 0 != $val ? ['data-activite-id' => $val, 'data-classe_activite-id' => $em->getRepository(Activite::class)->findOneById($val)->getClasseActivite()->getId(),
                    'data-type_activite-id' => $em->getRepository(Activite::class)->findOneById($val)->getClasseActivite()->getTypeActivite()->getId(), ]
                : ['data-activite-id' => 0, 'data-classe_activite-id' => 0, 'data-type_activite-id' => 0];
            },
            'attr' => ['class' => 'champRechercheDatatableInscription'],
            'required' => true,
        ]);

        $listeCampus = [
            'activite.recherche.option.lieu' => 0,
            'traduction.tous' => 0
        ];
        foreach ($em->getRepository(Etablissement::class)->findBy([], ['libelle' => 'asc']) as $etablissement) {
            $listeCampus[$etablissement->getLibelle()] = $etablissement->getId();
        }
        $builder->add('etablissement', ChoiceType::class, [
            'label_format' => 'common.lieu',
            'choices' => $listeCampus,
            'attr' => ['class' => 'champRechercheActivite'],
            'required' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_rechercheactivite';
    }
}
