<?php

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use UcaBundle\Entity\FormatAvecReservation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class FormatAvecReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('formatActivite',FormatActiviteType::class,[
                'data_class' => FormatAvecReservation::class])
            ->add('ressource', EntityType::class, [
                'class' => 'UcaBundle:Ressource',
                'choice_label' => 'libelle',
                'label_format' => 'format.reservation.ressource',
                'multiple' => true,
                'expanded' => false])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.save'
            ])
            ->add('reset', ResetType::class, [
                'label_format' => 'bouton.reset',
            ]);
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