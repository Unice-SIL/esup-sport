<?php

namespace UcaBundle\Controller\Api;

use UcaBundle\Entity\Creneau;
use UcaBundle\Entity\CreneauProfilUtilisateur;
use UcaBundle\Entity\DhtmlxDate;
use UcaBundle\Entity\DhtmlxEvenement;
use UcaBundle\Entity\DhtmlxSerie;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Entity\Lieu;
use UcaBundle\Entity\NiveauSportif;
use UcaBundle\Entity\ProfilUtilisateur;
use UcaBundle\Entity\Reservabilite;
use UcaBundle\Entity\Ressource;
use UcaBundle\Entity\Tarif;
use UcaBundle\Entity\Utilisateur;

class DhtmlxCommand
{
    public $data;
    private $em;
    private $action;
    private $item;
    private $commands;

    public function __construct($em, $data, $parent = null)
    {
        $this->em = $em;
        $this->data = $data;
        $this->commands = [];
        if (in_array($data['action'], ['insert', 'update', 'delete'])) {
            $this->action = $data['action'];
        } else {
            throw new \Exception("L'action ".$data['action']." n'est pas valide !");
        }
        $this->initItemAndCommands($parent);
    }

    public function getItem()
    {
        return $this->item;
    }

    public function execute()
    {
        if ('delete' == $this->action) {
            $this->em->remove($this->item);
        } else {
            $this->item->setDateDebut(new \DateTime($this->data['dateDebut']));
            $this->item->setDateFin(new \DateTime($this->data['dateFin']));
            if ('UcaBundle\Entity\DhtmlxEvenement' == get_class($this->item)) {
                $this->item->setDescription($this->data['text']);
                $this->item->setDependanceSerie(isset($this->data['dependanceSerie']) && 'true' == $this->data['dependanceSerie']);
                if (isset($this->data['eligible_bonus'])) {
                    if ('true' == $this->data['eligible_bonus']) {
                        $this->item->setEligibleBonus(true);
                    } else {
                        $this->item->setEligibleBonus(false);
                    }
                }
            } elseif ('UcaBundle\Entity\DhtmlxSerie' == get_class($this->item)) {
                if (isset($this->data['recurrence'])) {
                    $this->recurrence = $this->data['recurrence'];
                }
                $this->dateFinSerie = new \DateTime($this->data['dateFinSerie']);
            }
            if ($this->isCreneauEvent() && isset($this->data['capacite'])) {
                $this->getReferenceItem()->setCapacite($this->data['capacite']);
            }

            $this->updateTarif();
            $this->updateLieu();
            $this->updateProfils();
            $this->updateNiveauSportif();
            $this->updateEncadrants();
            $this->em->persist($this->item);
        }

        foreach ($this->commands as $command) {
            $command->execute();
        }
    }

    // Pb pour les évents simple
    public function getResult()
    {
        $res = $this->item->jsonSerialize();
        if ('delete' == $this->action) {
            $res['id'] = null;
        }
        if (!empty($res['evenements'])) {
            $res['enfants'] = $res['evenements'];
        }

        return $res;
    }

    private function initItemAndCommands($parent)
    {
        if ('insert' == $this->action && isset($this->data['enfants'])) {
            $this->item = new DhtmlxSerie($this->data);
            $this->createReference();
        } elseif ('insert' == $this->action && !isset($this->data['enfants'])) {
            $this->item = new DhtmlxEvenement($this->data);
            if (isset($this->data['eligible_bonus'])) {
                if ('true' == $this->data['eligible_bonus']) {
                    $this->item->setEligibleBonus(true);
                } else {
                    $this->item->setEligibleBonus(false);
                }
            }
            if (!empty($parent)) {
                $this->item->setSerie($parent->getItem());
                $parent->getItem()->addEvenement($this->item);
            }
            $this->createReference();
        } else {
            $this->item = $this->em->getRepository(DhtmlxDate::class)->find($this->data['id']);
        }

        if (isset($this->data['enfants'])) {
            foreach ($this->data['enfants'] as $enfant) {
                array_push($this->commands, new DhtmlxCommand($this->em, $enfant, $this));
            }
        }
        $this->item->oldId = $this->data['id'];
        $this->item->action = $this->data['action'];
    }

    private function isCreneauEvent()
    {
        return isset($this->data['enfants']) && (
            (isset($this->data['evenementType']) && 'creneau' == $this->data['evenementType'])
            || isset($this->data['creneau'])
            || (isset($this->data['reference_class']) && 'UcaBundle\Entity\FormatAvecCreneau' == $this->data['reference_class'])
        );
    }

    private function isRessourceEvent()
    {
        return !isset($this->data['enfants']) && (
            (isset($this->data['evenementType']) && 'ressource' == $this->data['evenementType'])
            || isset($this->data['reservabilite'])
            || (isset($this->data['reference_class']) && 'UcaBundle\Entity\Lieu' == $this->data['reference_class'])
            || (isset($this->data['reference_class']) && 'UcaBundle\Entity\Materiel' == $this->data['reference_class'])
        );
    }

    private function getReferenceItem()
    {
        if ($this->isCreneauEvent()) {
            return  $this->item->getCreneau();
        }
        if ($this->isRessourceEvent()) {
            return $this->item->getReservabilite();
        }

        return null;
    }

    private function createReference()
    {
        if ($this->isCreneauEvent()) {
            $c = new Creneau();
            $c->setFormatActivite($this->em->getReference(FormatActivite::class, $this->data['reference_id']));
            $this->item->setCreneau($c);
        } elseif ($this->isRessourceEvent()) {
            $r = new Reservabilite();
            $r->setRessource($this->em->getReference(Ressource::class, $this->data['reference_id']));
            $this->item->setReservabilite($r);
        }
    }

    private function updateTarif()
    {
        $item = $this->getReferenceItem();
        if (!empty($item) && $this->isCreneauEvent() && isset($this->data['tarif_id'])) {
            $item->setTarif($this->em->getReference(Tarif::class, $this->data['tarif_id']));
            $this->em->persist($item);
        }
    }

    private function updateLieu()
    {
        $item = $this->getReferenceItem();
        if (!empty($item) && $this->isCreneauEvent() && isset($this->data['lieu_id'])) {
            $item->setLieu($this->em->getReference(Lieu::class, $this->data['lieu_id']));
            $this->em->persist($item);
        }
    }

    private function updateProfils()
    {
        $item = $this->getReferenceItem();
        if (!empty($item) && method_exists($item, 'getProfilsUtilisateurs')) {
            if (null !== $item->getProfilsUtilisateurs()) {
                foreach ($item->getProfilsUtilisateurs() as $key => $profil) {
                    $item->removeProfilsUtilisateur($profil);
                    $this->em->remove($profil);
                }
                $this->em->flush();
            }
            if (isset($this->data['profil_ids']) && '' !== $this->data['profil_ids']) {
                foreach (explode(',', $this->data['profil_ids']) as $key => $profil) {
                    $keyStr = 'capaciteProfil_'.$profil;
                    $capaciteProfil = (isset($this->data[$keyStr]) && '' !== $this->data[$keyStr]) ? $this->data[$keyStr] : 0;
                    $creneauProfil = new CreneauProfilUtilisateur(
                        $item,
                        $this->em->getReference(ProfilUtilisateur::class, $profil),
                        $capaciteProfil
                    );

                    $item->addProfilsUtilisateur($creneauProfil);
                }
            }
            $this->em->flush();
        }
    }

    private function updateNiveauSportif()
    {
        $item = $this->getReferenceItem();

        if (!empty($item) && $this->isCreneauEvent()) {
            if (null !== $item->getNiveauxSportifs()) {
                foreach ($item->getNiveauxSportifs() as $key => $niveau) {
                    $item->removeNiveauSportif($niveau);
                }
            }
            if (isset($this->data['niveau_sportif_ids']) && '' !== $this->data['niveau_sportif_ids']) {
                foreach (explode(',', $this->data['niveau_sportif_ids']) as $key => $niveau) {
                    $item->addNiveauSportif($this->em->getReference(NiveauSportif::class, $niveau));
                }
            }
        }
    }

    private function updateEncadrants()
    {
        $item = $this->getReferenceItem();
        if (!empty($item) && $this->isCreneauEvent()) {
            if (null !== $item->getEncadrants()) {
                foreach ($item->getEncadrants() as $key => $encadrant) {
                    $item->removeEncadrant($encadrant);
                }
            }
            if (isset($this->data['encadrant_ids']) && '' !== $this->data['encadrant_ids']) {
                foreach (explode(',', $this->data['encadrant_ids']) as $key => $encadrant) {
                    $item->addEncadrant($this->em->getReference(Utilisateur::class, $encadrant));
                }
            }
        }
    }
}
