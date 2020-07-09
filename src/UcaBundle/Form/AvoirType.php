<?php

namespace UcaBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvoirType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $idCmd = $builder->getData()->getId();
        // le contrôle de commadne terminé se fera en amont
        $builder
            ->add('statut', HiddenType::class, [
                'data' => 'avoir',
            ])
            ->add('avoirCommandeDetails', EntityType::class, [
                'class' => 'UcaBundle:CommandeDetail',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'query_builder' => function (EntityRepository $er) use ($idCmd) {
                    $qb = $er->createQueryBuilder('cmdDetails');

                    return $qb->where($qb->expr()->andx(
                        $qb->expr()->eq('cmdDetails.commande', $idCmd),
                        $qb->expr()->gt('cmdDetails.montant', 0),
                        $qb->expr()->isNull('cmdDetails.referenceAvoir')
                        // Permet de retirer la cotisation sportive
                        // $qb->expr()->neq('cmdDetails.typeAutorisation', 2)
                        //$qb->expr()->not('cmdDetails.typeAutorisation=2')
                    ));
                },
                'label_format' => 'detailscommande.list.title',
                'choice_label' => function ($cmdDetails) {
                    return ucfirst($cmdDetails->getLibelle()).' - '.$cmdDetails->getMontant().'€';
                },
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.save',
            ])
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\Commande',
            'label' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_commande_avoir';
    }
}
