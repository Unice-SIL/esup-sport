<?php

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MaterielType extends AbstractType
{
   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('quantiteDisponible', NumberType::class, [
            'required' => true,
            'label_format' => 'ressource.quantite'
        ]);
        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save',
        ]);
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UcaBundle\Entity\Materiel'
        ));
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_materiel';
    }

    public function getParent()
    {
        return RessourceType::class;
    }
}
