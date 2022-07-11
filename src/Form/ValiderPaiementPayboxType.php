<?php

/*
 * Classe - ValiderPaiementPayboxType
 *
 * Formulaire de valdiation du paiement par l'utilsiateur
*/

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValiderPaiementPayboxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cgvAcceptees', CheckboxType::class, [
                'label' => 'mentions.informations.validation',
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'common.paiementcb',
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'ValiderPaiementPayboxType';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Uca\Commande',
            'validation_groups' => false,
        ]);
    }
}
