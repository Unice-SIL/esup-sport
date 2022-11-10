<?php

/*
 * Classe - FichierType
 *
 * Formulaire d'ajout/édition d'un Fichier
 * Le fichier est lié à une entité
*/

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class FichierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('imageFile', VichImageType::class, [
            'required' => true,
            'allow_delete' => false,
            'download_uri' => false,
            'image_uri' => false,
            'label_format' => 'referentiel.libelle.maj',
            'translation_domain' => 'messages',
            'attr' => [
                'class' => 'form-control-file'
            ]
        ]);
        $builder->add('save', SubmitType::class, [
            'label_format' => 'referentiel.bouton.maj',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Uca\Fichier',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_fichier';
    }
}
