<?php

/*
 * Classe - CommandeDetailInformationsCarteType
 *
 *  Formulaire d'édition des information d'une carte
*/

namespace UcaBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CommandeDetailInformationsCarteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $validation = $options['validation_groups'];
        $dtPaiement = ($options['date_paiement'])->format('Y-m-d');

        $builder
            ->add('numeroCarte', TextType::class, [
                'label_format' => 'commandedetail.informationscarte.numero',
                'constraints' => new Assert\NotBlank([
                    'groups' => $validation,
                    'message' => 'commandedetail.informationscarte.numero.notBlank',
                ]),
            ])
            ->add('dateCarteFinValidite', DateType::class, [
                'label_format' => 'commandedetail.informationscarte.datefin',
                'format' => 'dd/MM/yyyy',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y',
                    'data-datetimepicker-defaultdate' => '',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'groups' => $validation,
                        'message' => 'commandedetail.informationscarte.datefin.notBlank',
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'groups' => $validation,
                        'message' => 'commandedetail.informationscarte.datefin.incorrecte',
                        'value' => $dtPaiement,
                    ]),
                ],
            ])
            ->add('etablissementRetraitCarte', EntityType::class, [
                'label_format' => 'comamndedetail.informationscarte.etablissement',
                'class' => 'UcaBundle:Etablissement',
                'choice_label' => 'libelle',
                'placeholder' => 'commandedetail.informationscarte.etablissement',
                'constraints' => new Assert\NotBlank([
                    'groups' => $validation,
                    'message' => 'commandedetail.informationscarte.etablissement.notBlank',
                ]),
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.save',
                'validation_groups' => $validation,
            ])
      ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'UcaBundle\Entity\CommandeDetail',
            'validation_groups' => 'default',
            'csrf_protection' => false,
            'date_paiement' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_commandeDetail_achatCarte';
    }
}
