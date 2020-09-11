<?php

/*
 * Classe - InscriptionType
 *
 * Formulaire prennant en charge l'Inscription
 * Ce formulaire va gérér les uit
*/

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // $builder->add('statut', TextType::class, [
        //     'label_format' => 'common.libelle'
        // ]);
        $builder->add('autorisations', CollectionType::class, [
            'entry_type' => AutorisationType::class,
            'allow_add' => false,
            'label' => false,
        ]);
        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\Inscription',
        ]);
    }
}
