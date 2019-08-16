<?php

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use UcaBundle\Entity\FormatSimple;

class FormatSimpleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('formatActivite',FormatActiviteType::class,[
                'data_class' => FormatSimple::class])
            ->add('promouvoir',ChoiceType::class,[
                'choices'  => [
                    'Oui' => true,
                    'Non' => false,
                ],
                'label_format' => 'format.promouvoir',
                'expanded' => true,
                'multiple' => false, 
                'required' => true])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.save'
            ])
            ->add('reset', ResetType::class, [
                'label_format' => 'bouton.reset',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\FormatActivite',
            'sub_class'  => 'UcaBundle\Entity\FormatActiviteSimple'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_format_activite_simple';
    }
}