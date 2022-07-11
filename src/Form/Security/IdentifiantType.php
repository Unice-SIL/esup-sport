<?php

/*
 * Classe - IdentifiantType:
 *
 *  Formulaire de saisie de l'identifiant lors de l'oublie d'un mot de passe
*/

namespace App\Form\Security;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class IdentifiantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('identifiant', TextType::class, [
                'label_format' => 'resetting.request.field',
                'required' => true
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.ok',
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_password_forgotten';
    }
}
