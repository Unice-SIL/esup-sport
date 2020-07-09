<?php

namespace UcaBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nomenclatureRus', TextType::class, [
                'required' => false,
                'label_format' => 'ressource.code.rus',
                'disabled' => $options['data']->getSourceReferentiel(),
            ])
            ->add('etablissement', EntityType::class, [
                'class' => 'UcaBundle:Etablissement',
                'choice_label' => 'libelle',
                'label_format' => 'ressource.etablissement',
                'required' => true,
                'constraints' => new NotBlank(['message' => 'etablissement.notblank']),
                'multiple' => false,
                'expanded' => false,
                'disabled' => $options['data']->getSourceReferentiel(),
                'placeholder' => 'common.aucun',
            ])
            ->add('capaciteAccueil', TextType::class, [
                'required' => false,
                'label_format' => 'ressource.capacite',
            ])
            ->add('superficie', NumberType::class, [
                'required' => false,
                'label_format' => 'ressource.superficie',
                'disabled' => $options['data']->getSourceReferentiel(),
            ])
            ->add('latitude', NumberType::class, [
                'required' => false,
                'label_format' => 'ressource.latitude',
                'disabled' => $options['data']->getSourceReferentiel(),
            ])
            ->add('longitude', NumberType::class, [
                'required' => false,
                'label_format' => 'ressource.longitude',
                'disabled' => $options['data']->getSourceReferentiel(),
            ])
            ->add('adresse', TextType::class, [
                'label_format' => 'common.coordonnees.adresse',
            ])
            ->add('codePostal', TextType::class, [
                'label_format' => 'common.coordonnees.codepostal',
            ])
            ->add('ville', TextType::class, [
                'label_format' => 'common.coordonnees.ville',
            ])
            ->add('accesPMR', ChoiceType::class, [
                'label_format' => 'ressource.lieu.accesprm',
                'choices' => [
                    'common.oui' => 1,
                    'common.non' => 0,
                ],
                'placeholder' => 'common.nonrenseigne',
                'expanded' => true,
                'required' => false,
            ])
            ->add('imagesSupplementaires', CollectionType::class, [
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'label' => false,
                'required' => false,
                'entry_type' => ImageSupplementaireType::class,
                'by_reference' => false,
                'constraints' => new Valid(),
                'delete_empty' => true,
                'empty_data' => null,
            ])
            ->add('visiteVirtuelle', TextType::class, [
                'label_format' => 'lieu.visitevirtuelle.libelle',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\Lieu',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_lieu';
    }

    public function getParent()
    {
        return RessourceType::class;
    }
}
