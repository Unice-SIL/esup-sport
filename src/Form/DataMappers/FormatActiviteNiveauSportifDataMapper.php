<?php

namespace App\Form\DataMappers;

use Symfony\Component\Form\DataMapperInterface;
use App\Entity\Uca\FormatActiviteNiveauSportif;
use Doctrine\ORM\EntityManagerInterface;

class FormatActiviteNiveauSportifDataMapper implements DataMapperInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function mapDataToForms($data, $forms)
    {
        $forms = iterator_to_array($forms);

        $format = $forms['niveauSportif']->getParent()->getParent()->getData();
        if (is_object($format)) {
            $format = $format->getId();
        }
        $formatsNiveau = $this->em->getRepository(FormatActiviteNiveauSportif::class)->findBy(['formatActivite' => $format]);

        foreach ($formatsNiveau as $formatNiveau) {
            $dataNiveau[] = $formatNiveau->getNiveauSportif();
            $dataDetail[] = $formatNiveau->getDetail();
        }

        if (!empty($dataNiveau) && null === $forms['niveauSportif']->getData()) {
            $forms['niveauSportif']->setData($dataNiveau);
        }

        if (!empty($dataDetail) && null === $forms['detail']->getData()) {
            $forms['detail']->setData($dataDetail);
        }
    }

    public function mapFormsToData($forms, &$data)
    {
        $forms = iterator_to_array($forms);

        // getData() classe les objets
        $details = $forms['detail']->getData();
        $niveaux = $forms['niveauSportif']->getData();
        ksort($details);

        if (empty($details) && empty($niveaux)) {
            return;
        }

        $formatActivite = $forms['niveauSportif']->getParent()->getParent()->getData();

        foreach ($formatActivite->getNiveauxSportifs() as $niveauSportif) {
            $formatActivite->removeNiveauxSportif($niveauSportif);
            $this->em->remove($niveauSportif);
        }
        $this->em->flush();

        foreach ($niveaux as $niveau) {
            $detail = $details[$niveau->getid()] ?? '';
            $data = new FormatActiviteNiveauSportif($formatActivite, $niveau, $detail);
            $formatActivite->addNiveauxSportif($data);
        }
        $this->em->flush();
    }
}
