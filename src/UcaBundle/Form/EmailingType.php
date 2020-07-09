<?php

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EmailingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('objet', TextType::class, [
                'label_format' => 'contact.objet',
                'required' => true,
                'constraints' => new Assert\NotBlank(['groups' => 'default', 'message' => 'mail.objet.notBlank']),
            ])
            ->add('mail', TextareaType::class, [
                'label_format' => 'contact.message',
                'required' => true,
                'constraints' => new Assert\NotBlank(['groups' => 'default', 'message' => 'mail.message.notBlank']),
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'contact.save',
                'validation_groups' => 'default',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'validation_groups' => 'default',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_mail';
    }
}
