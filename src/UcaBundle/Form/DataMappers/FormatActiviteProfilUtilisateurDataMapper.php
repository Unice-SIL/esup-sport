<?php

namespace UcaBundle\Form\DataMappers;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataMapperInterface;
use UcaBundle\Entity\FormatActiviteProfilUtilisateur;

class FormatActiviteProfilUtilisateurDataMapper implements DataMapperInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function mapDataToForms($data, $forms)
    {
        $forms = iterator_to_array($forms);

        $format = $forms['profilUtilisateur']->getParent()->getParent()->getData();
        $formatsProfil = $this->em->getRepository('UcaBundle:FormatActiviteProfilUtilisateur')->findBy(['formatActivite' => $format]);

        foreach ($formatsProfil as $formatProfil) {
            $dataProfil[] = $formatProfil->getProfilUtilisateur();
            $dataCapacite[] = $formatProfil->getCapaciteProfil();
        }

        if (!empty($dataProfil) && null === $forms['profilUtilisateur']->getData()) {
            $forms['profilUtilisateur']->setData($dataProfil);
        }

        if (!empty($dataCapacite) && null === $forms['capaciteProfil']->getData()) {
            $forms['capaciteProfil']->setData($dataCapacite);
        }
    }

    public function mapFormsToData($forms, &$data)
    {
        $forms = iterator_to_array($forms);

        // getData() classe les objets
        $capacites = $forms['capaciteProfil']->getData();
        $profils = $forms['profilUtilisateur']->getData();
        ksort($capacites);

        if (empty($capacites) && empty($profils)) {
            return;
        }

        $formatActivite = $forms['profilUtilisateur']->getParent()->getParent()->getData();

        foreach ($formatActivite->getProfilsUtilisateurs() as $formatProfil) {
            $formatActivite->removeProfilsUtilisateur($formatProfil);
            $this->em->remove($formatProfil);
        }
        $this->em->flush();

        foreach ($profils as $profil) {
            $capacite = isset($capacites[$profil->getid()]) ? $capacites[$profil->getid()] : 0;
            $data = new FormatActiviteProfilUtilisateur($formatActivite, $profil, $capacite);
            $formatActivite->addProfilsUtilisateur($data);
        }
        $this->em->flush();
    }
}
