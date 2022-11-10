<?php

/*
 * Classe - UtilisateurCreditHistoriqueType

 * Formulaire d'ajout ou de report d'un crédit utilisateur
*/

namespace App\Form;

use App\Entity\Uca\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UtilisateurCreditHistoriqueType extends AbstractType
{
    private $utilisateur;

    private $montant;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            'data_class' => 'App\Entity\Uca\UtilisateurCreditHistorique',
        ]);
    }
}
