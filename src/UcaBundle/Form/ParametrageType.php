<?php

/*
 * Classe - ParametrageType
 *
 * Formulaire d'édition des paramêtres du site
*/

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParametrageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('mailContact', EmailType::class, [
            'label_format' => 'parametrage.mailcontact',
        ]);
        $builder->add('anneeUniversitaire', IntegerType::class, [
            'label_format' => 'parametrage.anneeUniversitaire',
        ]);
        $builder->add('lienFacebook', TextType::class, [
            'label_format' => 'parametrage.facebook',
        ]);
        $builder->add('lienInstagram', TextType::class, [
            'label_format' => 'parametrage.instagram',
        ]);
        $builder->add('lienYoutube', TextType::class, [
            'label_format' => 'parametrage.youtube',
        ]);
        $builder->add('timerPanier', IntegerType::class, [
            'label_format' => 'parametrage.timerpanier',
        ]);
        $builder->add('timerPanierApresValidation', IntegerType::class, [
            'label_format' => 'parametrage.timerpanierapresvalidation',
        ]);
        $builder->add('timerBds', IntegerType::class, [
            'label_format' => 'parametrage.timerbds',
        ]);
        $builder->add('timerCb', IntegerType::class, [
            'label_format' => 'parametrage.timercb',
        ]);
        $builder->add('timerPaybox', IntegerType::class, [
            'label_format' => 'parametrage.timerpaybox',
        ]);
        $builder->add('libelleAdresse', TextType::class, [
            'label_format' => 'parametrage.libelleadresse',
        ]);
        $builder->add('adresseFacturation', TextType::class, [
            'label_format' => 'parametrage.adressefacturation',
        ]);
        $builder->add('siret', IntegerType::class, [
            'label_format' => 'SIRET',
        ]);
        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\Parametrage',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_parametrage';
    }
}
