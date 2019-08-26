<?php

namespace UcaBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProfilUtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, [
                'label_format' => 'common.libelle'
            ])
            ->add('nbMaxInscriptions', TextType::class, [
                'label_format' => 'profilutilisateur.nbmaxinscriptions.libelle'
            ])
            ->add('preinscription', ChoiceType::class ,[
                'label_format' => 'profilutilisateur.preinscription',
                'choices' => [
                    'common.oui' => '1',
                    'common.non' => '0'
                ],
                'preferred_choices' => '1',
                'multiple' => false,
                'expanded' => true,
                'required' => true
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\ProfilUtilisateur'
        ]);
    }
}
