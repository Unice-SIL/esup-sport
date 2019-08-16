<?php

namespace UcaBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeAutorisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('libelle', TextType::class);
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
            'placeholder' => '- Aucun -',
            'attr' => [
                'class' => 'defaultToHide cotisationToShow caseToHide justificatifToHide achatToHide validationencadrantToHide'
            ]
        ]);
        $builder->add('informationsComplementaires', TextAreaType::class, [
            'required' => false,
            'label_format' => 'typeautorisation.informations.complementaires',
            'attr' => [
                'class' => 'defaultToHide cotisationToHide caseToShow justificatifToHide achatToHide validationencadrantToShow'
            ]
        ]);
        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\TypeAutorisation'
        ]);
    }
}
