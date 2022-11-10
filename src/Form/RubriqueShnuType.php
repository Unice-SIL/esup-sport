<?php

/*
 * Classe - highlight
 *
 * Formulaire d'ajout/édition d'un Highlight
*/

namespace App\Form;

use App\Entity\Uca\TypeRubrique;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Form\Type\VichImageType;

class RubriqueShnuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'required' => true,
                'label_format' => 'common.titre',
            ])
            ->add('texte', CKEditorType::class, [
                'required' => false,
                'label_format' => 'common.texte',
                'config' => [
                    'class' => 'ckeditor',
                ],
                'attr' => [
                    'class' => 'hiddenInput',
                ],
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => true,
                'allow_delete' => false,
                'download_uri' => false,
                'image_uri' => false,
                'label_format' => 'shnurubrique.image.libelle',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'form-control-file',
                ],
            ])
            ->add('lien', TextType::class, [
                'required' => false,
                'label_format' => 'common.external_link',
                'attr' => [
                    'class' => 'hiddenInput',
                ],
            ])
            ->add('type', EntityType::class, [
                'class' => TypeRubrique::class,
                'choice_label' => 'libelle',
                'label_format' => 'shnurubrique.type',
                'multiple' => false,
                'expanded' => false,
                'required' => true,
                'placeholder' => 'shnurubrique.select.type',
                'constraints' => new Assert\NotBlank(['message' => 'shnurubrique.type.notnull']),
            ])
        ;

        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Uca\ShnuRubrique',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_rubriqueshnu';
    }
}
