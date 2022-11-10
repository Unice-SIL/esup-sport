<?php

/*
 * Classe - IdentifiantType:
 *
 *  Formulaire de modification de mot de passe
*/

namespace App\Form\Security;

use App\Entity\Uca\Utilisateur;
use App\Validator\Constraints\OldPasswordConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\EqualTo;

class ChangementMotDePasseType extends AbstractType
{
    private $translator;
    private $passwordEncoder;

    public function __construct(TranslatorInterface $translator, UserPasswordHasherInterface $passwordEncoder) {
        $this->translator = $translator;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //  Si token n'est pas défini alors on est sur une modification de mot de passe s'il l'est c'est un oubli de mot de passe
        if (!!!$builder->getData()->getConfirmationToken()) {
            $builder->add('oldPassword', PasswordType::class, [
                'label' => 'utilisateur.ancien.motdepasse',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new OldPasswordConstraint()
                ]
            ]);

            $btnLabel = 'bouton.modifier';
        } else {
            $btnLabel = 'bouton.reinitialiser';
        }

        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'translation_domain' => 'FOSUserBundle',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'first_options' => ['label' => 'form.password'],
                'second_options' => ['label' => 'form.password_confirmation'],
                'invalid_message' => $this->translator->trans('utilisateur.change_password.mismatch'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => $btnLabel
            ])
        ;
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_password_forgotten';
    }
}
