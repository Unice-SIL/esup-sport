<?php

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Vich\UploaderBundle\Form\Type\VichImageType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;


class ActualiteType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('titre', TextType::class, [
            'label_format' => 'common.titre',
        ]);
        $builder->add('texte', CKEditorType::class, array(
            'label_format' => 'common.texte',
            'config' => array(
                'class' => 'ckeditor',
            )
        ));
        $builder->add('imageFile', VichImageType::class,[
            'required' => true,
            'allow_delete' => false,
            'download_uri' => false,
            'image_uri' => false,
            'label_format' => 'actualite.image.libelle',
            'translation_domain' => 'messages',
        ]);

        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save'
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
