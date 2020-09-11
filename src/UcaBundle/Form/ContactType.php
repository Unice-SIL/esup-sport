<?php

/*
 * Classe - ContactType
 *
 *  Formulaire de contact
*/

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label_format' => 'contact.email',
            ])
            ->add('objet', TextType::class, [
                'label_format' => 'contact.objet',
            ])
            ->add('message', TextareaType::class, [
                'label_format' => 'contact.message',
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'contact.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
