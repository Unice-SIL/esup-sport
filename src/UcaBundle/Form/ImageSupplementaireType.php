<?php

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ImageSupplementaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('imageFile', VichImageType::class, [
            'label' => false,
            'translation_domain' => 'messages',
            'required' => true,
            'allow_delete' => false,
            'download_uri' => false,
            'image_uri' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\ImageSupplementaire',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_imagesupplementaire';
    }
}
