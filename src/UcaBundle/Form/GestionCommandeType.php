<?php

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GestionCommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dateDebut', DateType::class, [
            'widget' => 'single_text',
            'label_format' => 'common.date.debut',
            'attr' => ['class' => 'champRechercheDatatableCommande'],
            'required' => false,
        ]);

        $builder->add('dateFin', DateType::class, [
            'widget' => 'single_text',
            'label_format' => 'common.date.fin',
            'attr' => ['class' => 'champRechercheDatatableCommande'],
            'required' => false,
        ]);

        $builder->add('datePaiement', DateType::class, [
            'widget' => 'single_text',
            'label_format' => 'common.datepaiement',
            'attr' => ['class' => 'champRechercheDatatableCommande'],
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_commande';
    }
}
