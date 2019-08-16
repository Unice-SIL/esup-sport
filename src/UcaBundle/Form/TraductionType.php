<?php

namespace UcaBundle\Form;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraductionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['data']['queryInfo']->getCols() as $alias => $col) {
            // if (strpos($col['config'], 'hidden') !== false) $class = HiddenType::class;
            // elseif (!empty($options['data']['data']['ckeditor'])) CKEditorType::class;
            // else $class = TextareaType::class;

            if (strpos($col['config'], 'hidden') === false) {
                $builder->add($alias, empty($options['data']['data']['ckeditor']) ? TextareaType::class : CKEditorType::class, [
                    'data' => $options['data']['data'][$alias],
                    'label' => "column.$alias",
                    'disabled' => strpos($col['config'], 'readonly') !== false,
                ]);
            }
        }
        $builder->add('save', SubmitType::class, [
            'label' => 'bouton.save'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => null]);
    }
}
