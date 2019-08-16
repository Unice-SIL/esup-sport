<?php

namespace UcaBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class EtablissementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('libelle', TextType::class, [
            'label_format' => 'common.libelle'
        ]);
        $builder->add('code', TextType::class, [
            'label_format' => 'etablissement.code'
        ]);
        $builder->add('adresse',TextType::class,[
            'label_format' => 'utilisateur.adresse'
        ]);
        $builder->add('codePostal',TextType::class,[
            'label_format' => 'utilisateur.codePostal'
        ]);
        $builder->add('ville',TextType::class,[
            'label_format' => 'utilisateur.ville'
        ]);
        $builder->add('email',EmailType::class,[
            'label_format' => 'etablissement.email.libelle',
            'required' => false
        ]);
        $builder->add('telephone',TextType::class,[
            'label_format' => 'etablissement.telephone.libelle',
            'required' => false,
            'attr' => ['placeholder' => 'common.telephone.placeholder']
        ]);
        $builder->add('horairesOuverture',TextAreaType::class,[
            'label_format' => 'etablissement.horaires.ouverture.libelle',
            'required' => false
        ]);
        $builder->add('imageFile',VichImageType::class,[
            'required' => true,
            'allow_delete' => false,
            'download_uri' => false,
            'image_uri' => false,
            'label_format' => 'etablissement.image.libelle',
            'translation_domain' => 'messages',
        ]);
        $builder->add('save', SubmitType::class, [
            'label_format' => 'bouton.save'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\Etablissement'
        ]);
    }
}
