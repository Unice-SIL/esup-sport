<?php

/*
 * Classe - AppelType:
 *
 *  Formulaire permetttant de faire l'appel
*/

namespace UcaBundle\Form;

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
            'data_class' => 'UcaBundle\Entity\Appel',
            'label' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_appel';
    }
}
