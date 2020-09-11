<?php

/*
 * Classe - FormatAvecCreneauType
 *
 * Formulaire d'ajout/édition d'un format avec creneau
 * Les creneau eux-memes sont gérés par la libraire DHTMLX
*/

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UcaBundle\Entity\FormatAvecCreneau;

class FormatAvecCreneauType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('formatActivite', FormatActiviteType::class, [
                'data_class' => FormatAvecCreneau::class,
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.save',
            ])
            ->add('previsualiser', SubmitType::class, [
                'label_format' => 'bouton.save.previsualiser',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\FormatAvecCreneau',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_format_activite_avec_creneau';
    }
}
