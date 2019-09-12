<?php

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class LieuType extends AbstractType
{
   

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder ->add('nomenclatureRus', TextType::class, [
            'required' => false,
            'label_format' => 'ressource.code.rus',
            'disabled' => $options['data']->getSourceReferentiel()
        ]);
        $builder ->add('etablissement', EntityType::class, [
            'class' => 'UcaBundle:Etablissement',
            'choice_label' => 'libelle',
            'label_format' => 'ressource.etablissement',
            'required' => false,
            'multiple' => false,
            'expanded' => false,
            'disabled' => $options['data']->getSourceReferentiel(),
            'placeholder' => 'common.aucun',
        ]);
        $builder ->add('capaciteAccueil', TextType::class, [
            'required' => false,
            'label_format' => 'ressource.capacite'
        ]);
        $builder ->add('superficie', NumberType::class, [
            'required' => false,
            'label_format' => 'ressource.superficie',
            'disabled' => $options['data']->getSourceReferentiel()
        ]);
        $builder ->add('latitude', NumberType::class, [
            'required' => false,
            'label_format' => 'ressource.latitude',
            'disabled' => $options['data']->getSourceReferentiel()
        ]);
        $builder ->add('longitude', NumberType::class, [
            'required' => false,
            'label_format' => 'ressource.longitude',
            'disabled' => $options['data']->getSourceReferentiel()
        ]);

        $builder->add('adresse',TextType::class,[
            'label_format' => 'utilisateur.adresse'
        ]);
        $builder->add('codePostal',TextType::class,[
            'label_format' => 'utilisateur.codepostal'
        ]);
        $builder->add('ville',TextType::class,[
            'label_format' => 'utilisateur.ville'
        ]);
        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save',
        ]);
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UcaBundle\Entity\Lieu'
        ));
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
