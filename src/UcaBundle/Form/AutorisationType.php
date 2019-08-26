<?php

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            if ($data->getCodeComportement() == 'case') {
                $form->add('caseACocher', CheckboxType::class, [
                    'label' => $data->getInformationsComplementaires()
                ]);
            } elseif ($data->getCodeComportement() == 'justificatif') {
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
            'data_class' => 'UcaBundle\Entity\Autorisation',
            'label' => false
        ]);
    }
}
