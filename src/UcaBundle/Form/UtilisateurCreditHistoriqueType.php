<?php

namespace UcaBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateurCreditHistoriqueType extends AbstractType
{
    private $utilisateur;

    private $montant;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('utilisateur', EntityType::class, [
                'class' => 'UcaBundle:Utilisateur',
                'expanded' => false,
                'multiple' => false,
                'disabled' => true,
                'label_format' => 'common.utilisateur',
            ])
            ->add('montant', NumberType::class, [
                'label_format' => 'common.montant',
                'required' => true,
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.enregistrer',
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'UcaGest_UtilisateurCreditHistorique';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\UtilisateurCreditHistorique',
        ]);
    }
}
