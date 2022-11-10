<?php

/*
 * Classe - AutorisationType
 *
 * Formulaire d'ajout/édition d'une autorisation à un utilisateur
 * Formulaire imbriqué
*/

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class AutorisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function ($event) {
            $infos = [];
            $data = $event->getData();
            $form = $event->getForm();
            if ('case' == $data->getCodeComportement()) {
                $form->add('caseACocher', CheckboxType::class, [
                    'label' => $data->getInformationsComplementaires(),
                ]);
            } elseif ('justificatif' == $data->getCodeComportement()) {
                $form->add('justificatifFile', VichFileType::class, [
                    'label' => $data->getInformationsComplementaires(),
                    'required' => true,
                    'allow_delete' => false,
                    'download_uri' => false,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Uca\Autorisation',
            'label' => false,
        ]);
    }
}
