<?php

/*
 * Classe - UtilisateurType
 *
 * Formulaire d'ajout/edition d'un utilisateur
*/

namespace App\Form;

use App\Entity\Uca\Groupe;
use App\Entity\Uca\Utilisateur;
use Doctrine\ORM\EntityRepository;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\StatutUtilisateur;
use Symfony\Component\Form\AbstractType;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Contracts\Translation\TranslatorInterface;

//use Symfony\Component\Form\FormEvents;
//use Symfony\Component\Form\FormEvent;

class UtilisateurType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $shibboleth = !empty($options['data']) && $options['data']->getShibboleth();
        $randomPassword = Utilisateur::getRandomPassword();
        if (empty($options['data'])) {
            $plainPasswordOptions = ['data' => $randomPassword, 'empty_data' => $randomPassword];
        } else {
            $plainPasswordOptions = ['empty_data' => $randomPassword];
        }

        $ajout = false;
        $modif = false;
        $preInscription = false;
        $profil = false;
        if ('modifier' == $options['action_type']) {
            $modif = true;
        } elseif ('preInscription' == $options['action_type']) {
            $preInscription = true;
        } elseif ('profil' == $options['action_type']) {
            $profil = true;
        } else {
            $ajout = true;
        }

        $builder
            ->add('username', null, [
                'label' => 'form.username',
                'translation_domain' => 'FOSUserBundle',
                'disabled' => ($modif or $shibboleth or $profil),
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.email',
                'translation_domain' => 'FOSUserBundle',
                'disabled' => $shibboleth,
            ])
            ->add('prenom', TextType::class, [
                'label_format' => 'utilisateur.prenom',
                'required' => true,
                'disabled' => $shibboleth,
            ])
            ->add('nom', TextType::class, [
                'label_format' => 'utilisateur.nom',
                'required' => true,
                'disabled' => $shibboleth,
            ])
            ->add('sexe', ChoiceType::class, [
                'label_format' => 'utilisateur.civilite',
                'choices' => [
                    'utilisateur.sexe.monsieur' => 'M',
                    'utilisateur.sexe.madame' => 'F',
                ],
                'preferred_choices' => 'M',
                'multiple' => false,
                'expanded' => true,
                'required' => true,
            ])
            ->add('dateNaissance', DateTimeType::class, [
                'label_format' => 'utilisateur.date.naissance',
                'required' => false,
                //'placeholder' => 'dd/MM/yyyy',
                'format' => 'dd/MM/yyyy',
                'html5' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y',
                    'data-datetimepicker-defaultdate' => '',
                ],
            ])
            ->add('adresse', TextType::class, [
                'label_format' => 'utilisateur.adresse',
                'required' => false,
            ])
            ->add('codePostal', TextType::class, [
                'label_format' => 'utilisateur.codepostal',
                'required' => false,
            ])
            ->add('ville', TextType::class, [
                'label_format' => 'utilisateur.ville',
                'required' => false,
            ])
            ->add('telephone', TextType::class, [
                'label_format' => 'utilisateur.telephone',
                'required' => false,
                'attr' => ['placeholder' => 'common.telephone.placeholder'],
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.enregistrer',
            ])
        ;

        if ($ajout) {
            $builder->add('plainPassword', HiddenType::class, $plainPasswordOptions);
        }
        if ($modif or $profil) {
            $builder->remove('plainPassword');
        }

        if ($preInscription) {
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
                ->add('profil', EntityType::class, [
                    'class' => ProfilUtilisateur::class,
                    'choice_label' => 'libelle',
                    'label_format' => 'utilisateur.profil',
                    'query_builder' => function (EntityRepository $er) {
                        return
                            $er->createQueryBuilder('pu')
                                ->andWhere('pu.preinscription = 1')
                                ->orderBy('pu.libelle', 'ASC')
                            ;
                    },
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true,
                    'placeholder' => 'utilisateur.select.profilutilisateur',
                    'disabled' => $shibboleth,
                ])
                ->add('documentFile', VichImageType::class, [
                    'required' => true,
                    'allow_delete' => false,
                    'download_uri' => false,
                    'image_uri' => false,
                    'label_format' => 'utilisateur.document.libelle',
                    'translation_domain' => 'messages',
                    'constraints' => new Assert\NotBlank(['message' => 'utilisateur.document.notBlank']),
                ])
                ->add('captcha', CaptchaType::class)
            ;
        } elseif (!$profil) {
            $builder
                ->add('statut', EntityType::class, [
                    'class' => StatutUtilisateur::class,
                    'choice_label' => 'libelle',
                    'label_format' => 'utilisateur.statut',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true,
                    'placeholder' => 'utilisateur.select.statututilisateur',
                    'constraints' => new Assert\NotBlank(['message' => 'utilisateur.statut.notnull']),
                ])
                ->add('matricule', TextType::class, [
                    'label_format' => 'utilisateur.matricule',
                    'required' => false,
                    'disabled' => $shibboleth,
                ])
                ->add('numeroNfc', TextType::class, [
                    'label_format' => 'utilisateur.numero.nfc',
                    'required' => false,
                ])
                ->add('profil', EntityType::class, [
                    'class' => ProfilUtilisateur::class,
                    'choice_label' => 'libelle',
                    'label_format' => 'utilisateur.profil',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true,
                    'placeholder' => 'utilisateur.select.profilutilisateur',
                    'disabled' => $shibboleth,
                ])
                ->add('groups', EntityType::class, [
                    'class' => Groupe::class,
                    'choice_label' => 'libelle',
                    'label_format' => 'utilisateur.droits',
                    'multiple' => true,
                    'expanded' => true,
                    'required' => false,
                    'placeholder' => 'utilisateur.droits',
                ])
                ->add('autorisations', EntityType::class, [
                    'class' => TypeAutorisation::class,
                    'choice_label' => 'libelle',
                    'label_format' => 'utilisateur.autorisations',
                    'group_by' => function (TypeAutorisation $ta) {
                        return $ta->getComportement()->getLibelle();
                    },
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false,
                ])
                ->add('description', TextareaType::class, [
                    'label_format' => 'utilisateur.description',
                    'required' => false,
                ])
            ;

            /*if ($preInscription) {
                $builder->addEventListener(
                FormEvents::POST_SUBMIT,
                function(FormEvent $event) {
                    $isValid = $event->getForm()->isValid();
                    if ($isValid) ;
                        return ;
                    }
                );
            }*/
        }
    }

    public function getBlockPrefix()
    {
        return 'ucaSport_Utilisateur';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Uca\Utilisateur',
            'action_type' => null,
        ]);
    }
}
