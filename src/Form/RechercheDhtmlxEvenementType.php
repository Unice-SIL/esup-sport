<?php

namespace App\Form;

use App\Entity\Uca\Etablissement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RechercheDhtmlxEvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $em = $options['data']['em'];

        $builder
            ->add('weekday', ChoiceType::class, [
                'label_format' => 'common.dayofweek',
                'choices' => [
                    'activite.recherche.option.dayofweek' => 0,
                    'traduction.tous' => 0,
                    'common.monday' => 2,
                    'common.tuesday' => 3,
                    'common.wednesday' => 4,
                    'common.thursday' => 5,
                    'common.friday' => 6,
                    'common.saturday' => 7,
                    'common.sunday' => 1,
                ],
                'required' => true,
            ])
            ->add('interval_time_start', TimeType::class, [
                'label_format' => 'activite.recherche.option.time.start',
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'H:i',
                ],
                'empty_data' => '00:00',
                'required' => true,
            ])
            ->add('interval_time_end', TimeType::class, [
                'label_format' => 'activite.recherche.option.time.end',
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'H:i',
                ],
                'empty_data' => '23:59',
                'required' => true,
            ])
        ;

        $listeCampus = [
            'traduction.tous' => 0,
        ];
        foreach ($em->getRepository(Etablissement::class)->findBy([], ['libelle' => 'asc']) as $etablissement) {
            $listeCampus[$etablissement->getLibelle()] = $etablissement->getId();
        }
        $builder->add('etablissement', ChoiceType::class, [
            'label_format' => 'common.lieu',
            'choices' => $listeCampus,
            'required' => false,
            'placeholder' => 'activite.recherche.option.lieu',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_recherchedhtmlxevenement';
    }
}
