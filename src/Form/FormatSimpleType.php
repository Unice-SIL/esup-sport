<?php

/*
 * Classe - FormatSimpleType
 *
 * Formulaire d'ajout/édition d'un format simple
*/

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Uca\FormatSimple;

class FormatSimpleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('formatActivite', FormatActiviteType::class, [
                'data_class' => FormatSimple::class, ])
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
            'data_class' => 'App\Entity\Uca\FormatActivite',
            'sub_class' => 'App\Entity\Uca\FormatActiviteSimple',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_format_activite_simple';
    }
}
