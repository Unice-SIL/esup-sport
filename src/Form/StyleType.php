<?php

namespace App\Form;

use App\Entity\Uca\Style;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StyleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('primaryColor', ColorType::class, [
                'label_format' => 'style.color.primary',
            ])
            ->add('primaryHover', PercentType::class, [
                'label_format' => 'style.hover',
                'help' => 'style.hover.help',
                'attr' => [
                    'min' => -1.0,
                    'max' => 1.0,
                    'step' => 0.1,
                ],
                'scale' => 2,
            ])
            ->add('primaryShadow', PercentType::class, [
                'label_format' => 'style.shadow',
                'help' => 'style.shadow.help',
                'attr' => [
                    'min' => -1.0,
                    'max' => 1.0,
                    'step' => 0.1,
                ],
                'scale' => 2,
            ])
            ->add('secondaryColor', ColorType::class, [
                'label_format' => 'style.color.secondary',
            ])
            ->add('secondaryHover', PercentType::class, [
                'label_format' => 'style.hover',
                'help' => 'style.hover.help',
                'attr' => [
                    'min' => -1.0,
                    'max' => 1.0,
                    'step' => 0.1,
                ],
                'scale' => 2,
            ])
            ->add('secondaryShadow', PercentType::class, [
                'label_format' => 'style.shadow',
                'help' => 'style.shadow.help',
                'attr' => [
                    'min' => -1.0,
                    'max' => 1.0,
                    'step' => 0.1,
                ],
                'scale' => 2,
            ])
            ->add('successColor', ColorType::class, [
                'label_format' => 'style.color.success',
            ])
            ->add('successHover', PercentType::class, [
                'label_format' => 'style.hover',
                'help' => 'style.hover.help',
                'attr' => [
                    'min' => -1.0,
                    'max' => 1.0,
                    'step' => 0.1,
                ],
                'scale' => 2,
            ])
            ->add('successShadow', PercentType::class, [
                'label_format' => 'style.shadow',
                'help' => 'style.shadow.help',
                'attr' => [
                    'min' => -1.0,
                    'max' => 1.0,
                    'step' => 0.1,
                ],
                'scale' => 2,
            ])
            ->add('warningColor', ColorType::class, [
                'label_format' => 'style.color.warning',
            ])
            ->add('warningHover', PercentType::class, [
                'label_format' => 'style.hover',
                'help' => 'style.hover.help',
                'attr' => [
                    'min' => -1.0,
                    'max' => 1.0,
                    'step' => 0.1,
                ],
                'scale' => 2,
            ])
            ->add('warningShadow', PercentType::class, [
                'label_format' => 'style.shadow',
                'help' => 'style.shadow.help',
                'attr' => [
                    'min' => -1.0,
                    'max' => 1.0,
                    'step' => 0.1,
                ],
                'scale' => 2,
            ])
            ->add('dangerColor', ColorType::class, [
                'label_format' => 'style.color.danger',
            ])
            ->add('dangerHover', PercentType::class, [
                'label_format' => 'style.hover',
                'help' => 'style.hover.help',
                'attr' => [
                    'min' => -1.0,
                    'max' => 1.0,
                    'step' => 0.1,
                ],
                'scale' => 2,
            ])
            ->add('dangerShadow', PercentType::class, [
                'label_format' => 'style.shadow',
                'help' => 'style.shadow.help',
                'attr' => [
                    'min' => -1.0,
                    'max' => 1.0,
                    'step' => 0.1,
                ],
                'scale' => 2,
            ])
            ->add('navbarBackgroundColor', ColorType::class, [
                'label_format' => 'style.background',
            ])
            ->add('navbarForegroundColor', ColorType::class, [
                'label_format' => 'style.foreground',
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.save.previsualiser'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Style::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_style';
    }
}
