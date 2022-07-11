<?php

/*
 * Classe - EvenementTYpe
 *
 *  Formulaire d'ajout/édition d'un évènement
*/

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('appels', CollectionType::class, [
                'entry_type' => AppelType::class,
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.save',
                'attr' => ['data-dismiss' => 'modal'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Uca\DhtmlxEvenement',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_evenement';
    }
}
