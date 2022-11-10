<?php

/*
 * Classe - AppelType:
 *
 *  Formulaire permetttant de faire l'appel
*/

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('present', CheckboxType::class, [
                'attr' => ['class' => 'checkbox-presence'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Uca\Appel',
            'label' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_appel';
    }
}
