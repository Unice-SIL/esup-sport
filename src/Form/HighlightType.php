<?php

/*
 * Classe - highlight
 *
 * Formulaire d'ajout/édition d'un Highlight
*/

namespace App\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class HighlightType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'required' => false,
                'label_format' => 'common.titre',
            ])
            ->add('intervenant', TextType::class, [
                'required' => false,
                'label_format' => 'highlight.intervenant',
            ])
            ->add('video', UrlType::class, [
                'required' => true,
                'label_format' => 'highlight.video.libelle',
            ])
            ->add('texte', CKEditorType::class, [
                'required' => false,
                'label_format' => 'common.texte',
                'config' => [
                    'class' => 'ckeditor',
                ],
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'image_uri' => false,
                'label_format' => 'highlight.video.miniature',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control-file'
                ]
            ])
        ;
        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_highlight_video';
    }
}
