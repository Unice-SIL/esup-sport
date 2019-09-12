<?php

namespace UcaBundle\Form;

use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Vich\UploaderBundle\Form\Type\VichImageType;
use UcaBundle\Entity\TypeAutorisation;
use UcaBundle\Entity\Utilisateur;
use Symfony\Component\Validator\Constraints as Assert;



//use Symfony\Component\Form\FormEvents;
//use Symfony\Component\Form\FormEvent;


class UtilisateurType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $shibboleth = !empty($options['data']) && $options['data']->getShibboleth();
        if (empty($options['data'])) $plainPasswordOptions = ['data' => Utilisateur::getRandomPassword()];
        else $plainPasswordOptions = [];

        $ajout = false;
        $modif = false;
        $preInscription = false;
        $profil = false;
        if ($options['action_type'] == "modifier") $modif = true;
        else if ($options['action_type'] == "preInscription") $preInscription = true;
        else if ($options['action_type'] == 'profil') $profil = true;
        else $ajout = true;

        $builder
            ->add('username', null, array(
                'label' => 'form.username',
                'translation_domain' => 'FOSUserBundle',
                'disabled' => ($modif or $shibboleth or $profil)
            ))
            ->add('email', EmailType::class, array(
                'label' => 'form.email',
                'translation_domain' => 'FOSUserBundle',
                'disabled' => $shibboleth
            ))
            ->add('prenom', TextType::class, [
                'label_format' => 'utilisateur.prenom',
                'required' => true,
                'disabled' => $shibboleth
            ])
            ->add('nom', TextType::class, [
                'label_format' => 'utilisateur.nom',
                'required' => true,
                'disabled' => $shibboleth
            ])
            ->add('sexe', ChoiceType::class, [
                'label_format' => 'utilisateur.civilite',
                'choices' => [
                    'utilisateur.sexe.monsieur' => 'M',
                    'utilisateur.sexe.madame' => 'F'
                ],
                'preferred_choices' => 'M',
                'multiple' => false,
                'expanded' => true,
                'required' => true
            ])
            ->add('dateNaissance', DateTimeType::class, [
                'label_format' => 'utilisateur.date.naissance',
                'required' => false,
                //'placeholder' => 'dd/MM/yyyy',
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'attr' => array(
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y',
                    'data-datetimepicker-defaultdate' => '',
                )
            ])
            ->add('adresse', TextType::class, [
                'label_format' => 'utilisateur.adresse',
                'required' => false
            ])
            ->add('codePostal', TextType::class, [
                'label_format' => 'utilisateur.codepostal',
                'required' => false
            ])
            ->add('ville', TextType::class, [
                'label_format' => 'utilisateur.ville',
                'required' => false
            ])
            ->add('telephone', TextType::class, [
                'label_format' => 'utilisateur.telephone',
                'required' => false,
                'attr' => ['placeholder' => 'common.telephone.placeholder']
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.enregistrer',
            ]);


        if ($ajout) $builder->add('plainPassword', HiddenType::class, $plainPasswordOptions);
        if ($modif or $profil) $builder->remove('plainPassword');
        
        if ($preInscription) {
            $builder
                ->add('plainPassword', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'options' => array(
                        'translation_domain' => 'FOSUserBundle',
                        'attr' => array('autocomplete' => 'new-password'),
                        ),
                    'first_options' => array('label' => 'form.password'),
                    'second_options' => array('label' => 'form.password_confirmation'),
                    'invalid_message' => 'fos_user.password.mismatch',
                ))
                ->add('profil', EntityType::class, [
                    'class' => 'UcaBundle:ProfilUtilisateur',
                    'choice_label' => 'libelle',
                    'label_format' => 'utilisateur.profil',
                    'query_builder' => function (EntityRepository $er) {
                        return
                            $er->createQueryBuilder('pu')
                            ->andWhere('pu.preinscription = 1')
                            ->orderBy('pu.libelle', 'ASC');
                    },
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true,
                    'placeholder' => 'utilisateur.select.profilutilisateur',
                    'disabled' => $shibboleth
                ])
                ->add('documentFile', VichImageType::class, [
                    'required' => true,
                    'allow_delete' => false,
                    'download_uri' => false,
                    'image_uri' => false,
                    'label_format' => 'utilisateur.document.libelle',
                    'translation_domain' => 'messages',
                    'constraints' =>  new Assert\NotBlank(['message' => 'utilisateur.document.notBlank'])
                ]);
        } elseif (!$profil) {
            $builder
                ->add('statut',EntityType::class, [
                    'class' => 'UcaBundle:StatutUtilisateur',
                    'choice_label' => 'libelle',
                    'label_format' => 'utilisateur.statut',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'placeholder' => 'utilisateur.select.statututilisateur',
                ])
                ->add('matricule', TextType::class, [
                    'label_format' => 'utilisateur.matricule',
                    'required' => false,
                    'disabled' => $shibboleth
                ])
                ->add('numeroNfc', TextType::class, [
                    'label_format' => 'utilisateur.numero.nfc',
                    'required' => false
                ])
                ->add('profil', EntityType::class, [
                    'class' => 'UcaBundle:ProfilUtilisateur',
                    'choice_label' => 'libelle',
                    'label_format' => 'utilisateur.profil',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true,
                    'placeholder' => 'utilisateur.select.profilutilisateur',
                    'disabled' => $shibboleth
                ])
                ->add('groups', EntityType::class, [
                    'class' => 'UcaBundle:Groupe',                 
                    'choice_label' => 'libelle',
                    'label_format' => 'utilisateur.droits',
                    'multiple' => true,
                    'expanded' => true,
                    'required' => false,
                    'placeholder' => "utilisateur.droits"
                ])
                ->add('autorisations', EntityType::class, [
                    'class' => 'UcaBundle:TypeAutorisation',
                    'choice_label' => 'libelle',
                    'label_format' => 'utilisateur.autorisations',
                    'group_by' => function (TypeAutorisation $ta) {
                        return $ta->getComportement()->getLibelle();
                    },
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false
                ])
                ->add('description', TextareaType::class, [
                    'label_format' => 'utilisateur.description',
                    'required' => false
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


    public function getParent()
    {
        return \FOS\UserBundle\Form\Type\RegistrationFormType::class;
    }

    public function getBlockPrefix()
    {
        return 'ucaSport_Utilisateur';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\Utilisateur',
            'action_type' => null,
        ]);
    }
}