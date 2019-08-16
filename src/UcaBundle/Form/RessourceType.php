<?php

namespace UcaBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class RessourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder ->add('libelle', TextType::class, [
            'disabled' => $options['data']->getSourceReferentiel(),
            'label_format' => 'common.libelle'
        ]);
        $builder ->add('description', TextareaType::class, [
            'required' => false,
            'label_format' => 'common.description'
        ]);
        $builder ->add('tarif', EntityType::class, [
            'class' => 'UcaBundle:Tarif',
            'choice_label' => 'libelle',
            'label_format' => 'ressource.tarif',
            'required' => false,
            'multiple' => false,
            'expanded' => false,
            'placeholder' => 'common.aucun',
        ]);
        $builder->add('imageFile',VichImageType::class,[
            'required' => true,
            'allow_delete' => false,
            'download_uri' => false,
            'image_uri' => false,
            'label_format' => 'ressource.image.libelle',
            'translation_domain' => 'messages',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UcaBundle\Entity\Ressource'
        ));
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_ressource';
    }
}
