<?php

/*
 * Classe - ProfilUtilisateurType
 *
 * Formulaire d'ajout/edition d'un profil utilisateur
*/

namespace App\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Uca\ProfilUtilisateur;

class ProfilUtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, [
                'label_format' => 'common.libelle',
            ])
            ->add('nbMaxInscriptions', TextType::class, [
                'label_format' => 'profilutilisateur.nbmaxinscriptions.libelle',
            ])
            ->add('nbMaxInscriptionsRessource', TextType::class, [
                'label_format' => 'profilutilisateur.nbmaxinscriptionsressource.libelle',
            ])
            ->add('parent', EntityType::class, [
                'label' => 'profilutilisateur.parent',
                'class' => ProfilUtilisateur::class,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->andWhere('p.parent is null')
                        ->orderBy('p.libelle', 'ASC')
                    ;
                },
                'choice_label' => 'libelle',
                'multiple' => false,
                'expanded' => false,
                'required' => false
            ])
            ->add('preinscription', ChoiceType::class, [
                'label_format' => 'profilutilisateur.preinscription',
                'choices' => [
                    'common.oui' => '1',
                    'common.non' => '0',
                ],
                'preferred_choices' => '1',
                'multiple' => false,
                'expanded' => true,
                'required' => true,
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Uca\ProfilUtilisateur',
        ]);
    }
}
