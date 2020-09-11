<?php

/*
 * Classe - ActualiteType:
 *
 *  Formulaire d'ajout/édition d'une actualité
*/

namespace UcaBundle\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ActualiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('titre', TextType::class, [
            'label_format' => 'common.titre',
        ]);
        $builder->add('texte', CKEditorType::class, [
            'label_format' => 'common.texte',
            'config' => [
                'class' => 'ckeditor',
            ],
        ]);
        $builder->add('imageFile', VichImageType::class, [
            'required' => true,
            'allow_delete' => false,
            'download_uri' => false,
            'image_uri' => false,
            'label_format' => 'actualite.image.libelle',
            'translation_domain' => 'messages',
        ]);

        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\Actualite',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_imagefond';
    }
}
