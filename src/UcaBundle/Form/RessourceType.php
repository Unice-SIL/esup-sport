<?php

/*
 * Classe - RessourceType
 *
 * Formulaire d'ajout/edition des éléments communs aux ressoruces
 * Formulaire imbriqué
*/

namespace UcaBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

class RessourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('libelle', TextType::class, [
            'disabled' => $options['data']->getSourceReferentiel(),
            'label_format' => 'common.libelle',
        ])
        ;
        $builder->add('description', TextareaType::class, [
            'required' => false,
            'label_format' => 'common.description',
        ])
        ;
        $builder->add('tarif', EntityType::class, [
            'class' => 'UcaBundle:Tarif',
            'choice_label' => 'libelle',
            'label_format' => 'ressource.tarif',
            'required' => true,
            'multiple' => false,
            'expanded' => false,
            'placeholder' => 'common.aucun',
            'constraints' => [
                new NotBlank()
            ]
        ])
        ;
        $builder->add('imageFile', VichImageType::class, [
            'required' => true,
            'allow_delete' => false,
            'download_uri' => false,
            'image_uri' => false,
            'label_format' => 'ressource.image.libelle',
            'translation_domain' => 'messages',
        ]);
        
        $builder->add(
            $builder->create('profils', RessourceProfilUtilisateurType::class, [
                'constraints' => new Assert\Valid(),
                'mapped' => false,
            ])
        );
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (!$view->vars['valid']) {
            ksort($view['profils']['capaciteProfil']->vars['value']);
            ksort($view['profils']['capaciteProfil']->vars['data']);
            ksort($view['profils']['capaciteProfil']->children);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\Ressource',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucabundle_ressource';
    }
}
