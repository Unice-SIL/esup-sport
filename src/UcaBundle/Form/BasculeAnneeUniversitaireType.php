<?php

/*
 * Classe - BasculeAnneeUniversitaireType
 *
 *  Formulaire de la bascule annnuelle.
 * Liste tous les champs présent pour cet écran
*/

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use UcaBundle\Entity\Activite;
use UcaBundle\Entity\ClasseActivite;
use UcaBundle\Entity\Lieu;
use UcaBundle\Entity\Materiel;

class BasculeAnneeUniversitaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $em = $options['data']['em'];
        $cmpt = 0;
        $classesActivites = $em->getRepository(ClasseActivite::class)->findAll();
        foreach ($classesActivites as $classeActivite) {
            ++$cmpt;
            $builder->add('ClasseAct-'.$classeActivite->getId(), CheckboxType::class, [
                'label_format' => $classeActivite->getLibelle(),
                'attr' => [
                    'class' => 'classe-activite classe-activite'.$classeActivite->getId(),
                ],
                'required' => false,
            ]);

            $activites = $em->getRepository(Activite::class)->findByClasseActivite($classeActivite->getId());
            foreach ($activites as $activite) {
                $cmpt += 2;
                $builder->add('Activite-'.$activite->getId().'-'.$classeActivite->getId(), CheckboxType::class, [
                    'label_format' => $activite->getLibelle(),
                    'attr' => [
                        'class' => 'activite activite'.$classeActivite->getId(),
                    ],
                    'required' => false,
                ]);

                $builder->add('optionCreneau-'.$activite->getId(), ChoiceType::class, [
                    'label' => false,
                    'choices' => [
                        'bascule.creneau.supprimer' => 0,
                        'bascule.creneau.dupliquer' => 1,
                    ],
                    'expanded' => true,
                    'multiple' => false,
                    'attr' => [
                        'class' => 'option-creneau'.$activite->getId(),
                    ],
                ]);
            }
        }
        $builder->add('nbClasseEtActivite', HiddenType::class, [
            'data' => $cmpt--,
            'attr' => [
                'class' => 'hidden',
            ],
        ]);

        $listeLieu = $em->getRepository(Lieu::class)->findAll();
        foreach ($listeLieu as $lieu) {
            $builder->add('Lieu-'.$lieu->getId(), CheckboxType::class, [
                'label_format' => $lieu->getLibelle(),
                'attr' => [
                    'class' => 'lieu lieu'.$lieu->getId(),
                ],
                'required' => false,
            ]);
        }
        $builder->add('nbLieu', HiddenType::class, [
            'data' => sizeof($listeLieu),
            'attr' => [
                'class' => 'hidden',
            ],
        ]);

        $listeMateriel = $em->getRepository(Materiel::class)->findAll();
        foreach ($listeMateriel as $materiel) {
            $builder->add('Materiel-'.$materiel->getId(), CheckboxType::class, [
                'label_format' => $materiel->getLibelle(),
                'attr' => [
                    'class' => 'materiel materiel'.$materiel->getId(),
                ],
                'required' => false,
            ]);
        }
        $builder->add('nbMateriel', HiddenType::class, [
            'data' => sizeof($listeMateriel),
            'attr' => [
                'class' => 'hidden',
            ],
        ]);

        $builder
            ->add('basculeDesEvenements', CheckboxType::class, [
                'label' => 'bascule.evenement.statut',
                'required' => false,
            ])
            ->add('basculeDesReservations', CheckboxType::class, [
                'label' => 'bascule.reservation.supprimer',
                'required' => false,
            ])
            ->add('dupliquerFormatAvecReservation', CheckboxType::class, [
                'label' => 'Dupliquer les formats de réservation de salles et d\'équipements',
                'required' => false,
            ])
            ->add('basculeCarteEtCotisation', CheckboxType::class, [
                'label' => 'bascule.carte.cotisation.supprimer',
                'required' => false,
            ])
            ->add('dupliquerFormatAchatCarte', CheckboxType::class, [
                'label' => 'Dupliquer les formats d\'achat de carte',
                'required' => false,
            ])
            ->add('basculeCredit', CheckboxType::class, [
                'label_format' => 'utilisateur.credit.bascule.supprimer',
            ])
            ->add('nouvelleDateDebutInscription', DateTimeType::class, [
                'label_format' => 'format.date.inscription.debut',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y H:i',
                ],
                'constraints' => new Assert\NotBlank(['groups' => 'default', 'message' => 'formatactivite.dateDebutInscription.notblank']),
            ])
            ->add('nouvelleDateFinInscription', DateTimeType::class, [
                'label_format' => 'format.date.inscription.fin',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y H:i',
                ],
                'constraints' => new Assert\NotBlank(['groups' => 'default', 'message' => 'formatactivite.dateFinInscription.notblank']),
            ])
            ->add('nouvelleDateDebutEffective', DateTimeType::class, [
                'label_format' => 'format.date.effective.debut',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y H:i',
                ],
                'constraints' => new Assert\NotBlank(['groups' => 'default', 'message' => 'formatactivite.dateDebutEffective.notblank']),
            ])
            ->add('nouvelleDateFinEffective', DateTimeType::class, [
                'label_format' => 'format.date.effective.fin',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'class' => 'datetimepicker',
                    'data-datetimepicker-format' => 'd/m/Y H:i',
                ],
                'constraints' => new Assert\NotBlank(['groups' => 'default', 'message' => 'formatactivite.dateFinEffective.notblank']),
            ])

            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.basculer',
                'validation_groups' => 'default',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'liste_activites' => null,
            'validation_groups' => 'default',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_bascule_activites';
    }
}
