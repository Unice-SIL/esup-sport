<?php

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BasculeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $liste = $options['liste_activites'];
        $builder
            ->add('activites', ChoiceType::class, [
                'choices' => $liste,
                'label_format' => 'activites.liste',
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'constraints' => new Assert\NotBlank(['groups' => 'default', 'message' => 'activite.bascule.notBlank']),
            ])
            ->add('nouvelleDateDebutInscription', DateTimeType::class, [
                'label_format' => 'format.date.inscription.debut',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y H:i',
                ],
                'constraints' => new Assert\NotBlank(['groups' => 'default', 'message' => 'formatactivite.dateDebutInscription.notblank']),
            ])
            ->add('nouvelleDateFinInscription', DateTimeType::class, [
                'label_format' => 'format.date.inscription.fin',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y H:i',
                ],
                'constraints' => new Assert\NotBlank(['groups' => 'default', 'message' => 'formatactivite.dateFinInscription.notblank']),
            ])
            ->add('nouvelleDateDebutEffective', DateTimeType::class, [
                'label_format' => 'format.date.effective.debut',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y H:i',
                ],
                'constraints' => new Assert\NotBlank(['groups' => 'default', 'message' => 'formatactivite.dateDebutEffective.notblank']),
            ])
            ->add('nouvelleDateFinEffective', DateTimeType::class, [
                'label_format' => 'format.date.effective.fin',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y H:i',
                ],
                'constraints' => new Assert\NotBlank(['groups' => 'default', 'message' => 'formatactivite.dateFinEffective.notblank']),
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.basculer',
                'validation_groups' => 'default',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'liste_activites' => null,
            'validation_groups' => 'default',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_bascule_activites';
    }
}
