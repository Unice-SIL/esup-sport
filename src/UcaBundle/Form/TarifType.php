<?php

/*
 * Classe - TarifType
 *
 * Formulaire d'ajout/edition un tarif
*/

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TarifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('libelle', TextType::class, [
            'label_format' => 'common.libelle',
        ]);

        $builder->add('montants', CollectionType::class, [
            'entry_type' => MontantTarifProfilUtilisateurType::class,
            'label_format' => 'tarif.montants',
            'allow_add' => false,
            'prototype' => true,
        ]);

        $builder->add('pourcentageTVA', PercentType::class, [
            'label' => 'common.tva',
            'type' => 'integer',
            'empty_data' => '0',
        ]);
        $builder->add('tva', CheckboxType::class, [
            'label' => 'tarif.tva.nonapplicable',
        ]);

        $builder->add('tvaNonApplicable', TextareaType::class, [
            'label_format' => 'tarif.tva.raison',
        ]);

        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\Tarif',
        ]);
    }
}
