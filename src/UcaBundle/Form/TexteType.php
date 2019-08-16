<?php

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;


class TexteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('titre', TextType::class, [
            'label_format' => 'texte.titre',
        ]);

        $builder->add('mobile', ChoiceType::class, [
            'choices'  => [
                'Textes identiques sur Desktop et Mobile' => 0,
                'Textes différents sur Desktop et Mobile' => 1,
                'Texte à afficher seulement sur Desktop' => 2,
            ],
        ]);

        $builder->add('texte', CKEditorType::class, array(
            'config' => array(
                'class' => 'ckeditor',
            )
        ));

        $builder->add('texteMobile', CKEditorType::class, array(
            'config' => array(
                'class' => 'ckeditor',
                'id' => 'inputTexteMobile',
                'data' => 'bla'
            ),
        ));
        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save',
        ]);
        $builder->add('reset', ResetType::class, [
            'label_format' => 'bouton.reset',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UcaBundle\Entity\Texte'
        ));
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_texte';
    }
}
