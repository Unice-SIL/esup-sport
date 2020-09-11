<?php

/*
 * Classe - MaterielType
 *
 * Formulaire d'ajout/edition d'un materiel
*/

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaterielType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('quantiteDisponible', TextType::class, [
            'required' => true,
            'label_format' => 'ressource.quantite',
        ]);
        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\Materiel',
        ]);
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
