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


class ActiviteType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('libelle', TextType::class, [
            'label_format' => 'common.libelle'
        ]);
        $builder->add('description', TextareaType::class, [
            'label_format' => 'common.description'
        ]);
        $builder->add('classeActivite', EntityType::class, [
            'class' => 'UcaBundle:ClasseActivite',
            'choice_label' => 'libelle',
            'label_format' => 'classeactivite.libelle',
            'multiple' => false,
            'expanded' => false,
            'placeholder' => 'activite.classeactivite.placeholder'
        ]);
        $builder->add('imageFile',VichImageType::class,[
            'required' => true,
            'allow_delete' => false,
            'download_uri' => false,
            'image_uri' => false,
            'label_format' => 'activite.image.libelle',
            'translation_domain' => 'messages',
        ]);

        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save'
        ]);
        // $builder->add('reset', ResetType::class, [
        //     'label_format' => 'bouton.reset',
        // ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\Activite',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_activite';
    }
}
