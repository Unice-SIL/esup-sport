<?php

/*
 * Classe - LogoPartenaireType
 *
 * Formulaire d'édition d'un logo partenarie
*/

namespace App\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class LogoPartenaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('imageFile', VichImageType::class, [
            'required' => true,
            'allow_delete' => false,
            'download_uri' => false,
            'image_uri' => false,
            'label_format' => 'imagefond.image.libelle',
            'translation_domain' => 'messages',
            'attr' => [
                'class' => 'form-control-file'
            ]
        ]);
        $builder->add('nom', TextType::class, [
            'label_format' => 'logopartenaire.nom',
        ]);
        $builder->add('lien', TextType::class, [
            'label_format' => 'logopartenaire.lien',
            'required' => false,
        ]);
        $builder->add('description', CKEditorType::class, [
            'config' => [
                'class' => 'ckeditor',
            ],
            'required' => false,
        ]);

        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Uca\LogoPartenaire',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_logopartenaire';
    }
}
