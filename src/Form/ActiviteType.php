<?php

/*
 * Classe - ActiviteType:
 *
 *  Formulaire d'ajout/édition d'une activité
*/

namespace App\Form;

use App\Entity\Uca\ClasseActivite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('libelle', TextType::class, [
            'label_format' => 'common.libelle',
        ]);
        $builder->add('description', TextareaType::class, [
            'label_format' => 'common.description',
        ]);
        $builder->add('classeActivite', EntityType::class, [
            'class' => ClasseActivite::class,
            'choice_label' => 'libelle',
            'label_format' => 'classeactivite.libelle',
            'multiple' => false,
            'expanded' => false,
            'placeholder' => 'activite.classeactivite.placeholder',
        ]);
        $builder->add('imageFile', VichImageType::class, [
            'required' => true,
            'allow_delete' => false,
            'download_uri' => false,
            'image_uri' => false,
            'label_format' => 'activite.image.libelle',
            'translation_domain' => 'messages',
            'attr' => [
                'class' => 'form-control-file'
            ]
        ]);

        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Uca\Activite',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_activite';
    }
}
