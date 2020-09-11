<?php

/*
 * Classe - FormatAvecReservationType
 *
 * Formulaire d'ajout/édition d'un format avec réservation
*/

namespace UcaBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UcaBundle\Entity\FormatAvecReservation;

class FormatAvecReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('formatActivite', FormatActiviteType::class, [
                'data_class' => FormatAvecReservation::class, ])
            ->add('ressource', EntityType::class, [
                'class' => 'UcaBundle:Ressource',
                'choice_label' => 'libelle',
                'label_format' => 'format.reservation.ressource',
                'multiple' => true,
                'expanded' => false, ])
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
            'data_class' => 'UcaBundle\Entity\FormatAvecReservation',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_format_activite_avec_reservation';
    }
}
