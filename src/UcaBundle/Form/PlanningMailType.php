<?php

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PlanningMailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $liste = $options['liste_destinataires'];
        $builder
            ->add('destinataires', ChoiceType::class, [
                'choices' => $liste,
                //'choice_attr' => ['checked' => 'checked'],
                'label_format' => 'contact.destinataires',
                'multiple' => true,
                'expanded' => true,
                'required' => true,
                'constraints' => new Assert\NotBlank(['message' => 'mail.destinataires.notBlank']),
            ])

            ->add('objet', TextType::class, [
                'label_format' => 'contact.objet',
                'constraints' => new Assert\NotBlank(['message' => 'mail.objet.notBlank']),
            ])
            ->add('mail', TextareaType::class, [
                'label_format' => 'contact.message',
                'constraints' => new Assert\NotBlank(['message' => 'mail.message.notBlank']),
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'contact.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'liste_destinataires' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_mail';
    }
}
