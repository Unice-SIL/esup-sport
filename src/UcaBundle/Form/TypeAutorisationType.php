<?php

/*
 * Classe - TypeActiviteType
 *
 * Formulaire d'ajout/édition d'un type d'activité
*/

namespace UcaBundle\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeAutorisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('libelle', TextType::class, [
            'label_format' => 'common.libelle',
        ]);
        $builder->add('comportement', EntityType::class, [
            'class' => 'UcaBundle:ComportementAutorisation',
            'choice_label' => 'libelle',
            'choice_name' => 'codeComportement',
            'choice_attr' => function ($choice, $key, $value) {
                return ['data-code' => $choice->getCodeComportement()];
            },
            'label_format' => 'typeautorisation.comportement',
            'required' => true,
            'multiple' => false,
            'expanded' => false,
            'placeholder' => 'common.aucun',
        ]);
        $builder->add('tarif', EntityType::class, [
            'class' => 'UcaBundle:Tarif',
            'choice_label' => 'libelle',
            'label_format' => 'typeautorisation.tarif',
            'required' => false,
            'multiple' => false,
            'expanded' => false,
            'disabled' => false,
            'placeholder' => 'common.aucun',
            'attr' => [
                'class' => 'defaultToHide cotisationToShow caseToHide justificatifToHide carteToShow validationencadrantToHide validationgestionnaireToHide',
            ],
        ]);
        $builder->add('informationsComplementaires', CKEditorType::class, [
            'required' => false,
            'label_format' => 'typeautorisation.informations.complementaires',
            'attr' => [
                'class' => 'ckeditor defaultToHide cotisationToShow caseToShow justificatifToShow carteToShow validationencadrantToShow validationgestionnaireToShow',
            ],
        ]);
        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\TypeAutorisation',
        ]);
    }
}
