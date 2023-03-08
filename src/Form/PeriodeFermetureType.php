<?php

namespace App\Form;

use App\Entity\Uca\PeriodeFermeture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PeriodeFermetureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDeb', DateType::class, [
                'label_format' => 'periodefermeture.datedeb',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y',
                ],
            ])
            ->add('dateFin', DateType::class, [
                'label_format' => 'periodefermeture.datefin',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y',
                ],
            ])
            ->add('description', TextType::class, [
                'label_format' => 'periodefermeture.description',
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PeriodeFermeture::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_periodefermeture';
    }
}
