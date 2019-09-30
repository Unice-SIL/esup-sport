<?php

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateurMentionsInformationsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mentionsInformationsAcceptees', CheckboxType::class, [
                'label' => 'mentions.informations.validation'
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.enregistrer',
            ]);
    }

    public function getBlockPrefix()
    {
        return 'UtilisateurMentionsInformationsType';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\Utilisateur',
            'validation_groups' => false,
        ]);
    }
}
