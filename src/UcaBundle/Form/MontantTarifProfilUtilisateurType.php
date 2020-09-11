<?php
/*
 * Classe - MontantTarifProfilUtilisateur
 *
 * Formulaire d'ajout/edition d'un tarif en fonction du MontantTarifProfilUtilisateur
 * Formulaire imbriqué
*/

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MontantTarifProfilUtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('montant', MoneyType::class, [
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\MontantTarifProfilUtilisateur',
            'label' => false,
        ]);
    }
}
