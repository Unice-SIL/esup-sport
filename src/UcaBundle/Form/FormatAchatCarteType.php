<?php

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use UcaBundle\Entity\FormatAchatCarte;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class FormatAchatCarteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('formatActivite', FormatActiviteType::class, [
                'data_class' => FormatAchatCarte::class
            ])
            ->add('carte', EntityType::class, [
                'class' => 'UcaBundle:TypeAutorisation',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('ta')
                        ->andWhere('ta.comportement=4')
                        ->orderBy('ta.libelle', 'ASC');
                },
                'choice_label' => function($typeAutorisation){
                    return ucfirst($typeAutorisation->getLibelle());
                },
                'label_format' => 'format.achat.carte.autorisation',
                'multiple' => false,
                'expanded' => false,
                'required' => true,
                'placeholder' => 'formatachatcarte.carte.placeholder'
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.save'
            ])
            ->add('previsualiser', SubmitType::class, [
                'label_format' => 'bouton.save.previsualiser'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\FormatAchatCarte',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_format_activite_achat_carte';
    }
}