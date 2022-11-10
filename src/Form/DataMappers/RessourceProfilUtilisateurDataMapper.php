<?php

namespace App\Form\DataMappers;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataMapperInterface;
use App\Entity\Uca\RessourceProfilUtilisateur;

class RessourceProfilUtilisateurDataMapper implements DataMapperInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function mapDataToForms($data, $forms)
    {
        $forms = iterator_to_array($forms);

        $ressource = $forms['profilUtilisateur']->getParent()->getParent()->getData();
        if (is_object($ressource)) {
            $ressource = $ressource->getId();
        }
        $ressourcesProfil = $this->em->getRepository(RessourceProfilUtilisateur::class)->findBy(['ressource' => $ressource]);
        
        foreach ($ressourcesProfil as $ressource) {
            $dataProfil[] = $ressource->getProfilUtilisateur();
            $dataCapacite[] = $ressource->getCapaciteProfil();
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

        $ressource = $forms['profilUtilisateur']->getParent()->getParent()->getData();

        foreach ($ressource->getProfilsUtilisateurs() as $profilUtilisateur) {
            $ressource->removeProfilsUtilisateur($profilUtilisateur);
            $this->em->remove($profilUtilisateur);
        }
        $this->em->flush();

        foreach ($profils as $profil) {
            $capacite = isset($capacites[$profil->getid()]) ? $capacites[$profil->getid()] : 0;
            $data = new RessourceProfilUtilisateur($ressource, $profil, $capacite);
            $ressource->addProfilsUtilisateur($data);
        }
        $this->em->flush();
    }
}
