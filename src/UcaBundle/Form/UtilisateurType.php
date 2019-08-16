<?php

namespace UcaBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UcaBundle\Entity\TypeAutorisation;
use UcaBundle\Entity\Utilisateur;

class UtilisateurType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $shibboleth = !empty($options['data']) && $options['data']->getShibboleth();
        if (empty($options['data'])) $plainPasswordOptions = ['data' => Utilisateur::getRandomPassword()];
        else $plainPasswordOptions = [];
        $builder->add('plainPassword', HiddenType::class, $plainPasswordOptions);


        $builder
            ->add('username', null, [
                'label' => 'form.username', 'translation_domain' => 'FOSUserBundle',
                'disabled' => $shibboleth
            ])
            ->add('email', EmailType::class, [
                'label' => 'form.email', 'translation_domain' => 'FOSUserBundle',
                'disabled' => $shibboleth
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
                'multiple' => false,
                'expanded' => true,
                'required' => true
            ])
            ->add('dateNaissance', BirthdayType::class, [
                'label_format' => 'utilisateur.date.naissance',
                'required' => false,
                'widget' => 'single_text'
            ])
            ->add('adresse', TextType::class, [
                'label_format' => 'utilisateur.adresse',
                'required' => false
            ])
            ->add('codePostal', TextType::class, [
                'label_format' => 'utilisateur.codePostal',
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
            ->add('groups', EntityType::class, [
                'class' => 'UcaBundle:Groupe',
                'choice_label' => 'name',
                'label_format' => 'utilisateur.droits',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'placeholder' => "utilisateur.droits"
            ])
            ->add('description', TextareaType::class, [
                'label_format' => 'utilisateur.description',
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.enregistrer',
            ])
            ->add('reset', ResetType::class, [
                'label_format' => 'bouton.reinitialiser'
            ]);
    }

    public function getParent()
    {
        return \FOS\UserBundle\Form\Type\RegistrationFormType::class;
    }

    public function getBlockPrefix()
    {
        return 'ucaSport_registration';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\Utilisateur',
            'action_type' =>  null
        ]);
    }
}
