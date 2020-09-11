<?php

/*
 * Classe - ClasseActiviteType:
 *
 * Formulaire d'ajout/édition d'une activité
*/

namespace UcaBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ClasseActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, [
                'label_format' => 'common.libelle',
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => true,
                'allow_delete' => false,
                'download_uri' => false,
                'image_uri' => false,
                'label_format' => 'classeactivite.image.libelle',
                'translation_domain' => 'messages',
            ])
            ->add('typeActivite', EntityType::class, [
                'class' => 'UcaBundle:TypeActivite',
                'choice_label' => 'libelle',
                'label_format' => 'typeactivite.libelle',
                'multiple' => false,
                'expanded' => false,
                'placeholder' => 'classeactivite.typeactivite.placeholder', ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\ClasseActivite',
        ]);
    }
}
