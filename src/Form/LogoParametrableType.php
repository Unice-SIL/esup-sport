<?php

/*
 * Classe - LogoParametrableType
 *
 * Formulaire d'édition d'un logo partenarie
*/

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class LogoParametrableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('imageFile', VichImageType::class, [
            'required' => true,
            'allow_delete' => false,
            'download_uri' => false,
            'image_uri' => false,
            'label_format' => 'logoparametrable.image.libelle',
            'translation_domain' => 'messages',
            'attr' => [
                'class' => 'form-control-file'
            ]
        ])
        ->add('actif', CheckboxType::class, [
            'required' => false,
            'label_format' => 'logoparametrable.actif.libelle',
        ])
        ;

        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Uca\LogoParametrable',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_logoparametrable';
    }
}
