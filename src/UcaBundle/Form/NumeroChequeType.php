<?php

/*
 * Classe - NumeroChequeType
 *
 * Formulaire d'édition du numéro de chèque
*/

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class NumeroChequeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $validation = $options['validation_groups'];
        $builder->add('numeroCheque', TextType::class, [
            'label_format' => 'commande.numero.cheque',
            'required' => true,
            'constraints' => new Assert\NotBlank([
                'groups' => $validation,
                'message' => 'commande.numero.cheque.notBlank',
            ]),
        ])
            ->add('save', SubmitType::class, [
                'label_format' => 'common.oui',
                'validation_groups' => $validation,
            ])
      ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'validation_groups' => 'default',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_numeroCheque';
    }
}
